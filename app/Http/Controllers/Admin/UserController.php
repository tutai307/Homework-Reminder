<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Constructor - chỉ admin mới truy cập được
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                abort(403, 'Bạn không có quyền truy cập tính năng này.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $classId = $request->get('class_id');
        $classes = ClassModel::withCount('users')->orderBy('name')->get();

        if ($classId) {
            $query = User::with(['roles', 'classes']);
            $selectedClass = null;

            if ($classId === 'none') {
                $query->whereDoesntHave('classes');
                $selectedClass = (object)['name' => 'Chưa gán lớp'];
            } elseif ($classId === 'all') {
                // Không filter theo lớp
                $selectedClass = (object)['name' => 'Tất cả người dùng'];
            } else {
                $query->whereHas('classes', function($q) use ($classId) {
                    $q->where('classes.id', $classId);
                });
                $selectedClass = ClassModel::find($classId);
            }

            $users = $query->latest()->paginate(20);
            return view('admin.users.index', compact('users', 'classes', 'selectedClass', 'classId'));
        }

        return view('admin.users.index', compact('classes', 'classId'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request)
    {
        $classes = ClassModel::orderBy('name')->get();
        $preselectedClassId = $request->get('class_id');
        
        // Chuyển đổi 'all' hoặc 'none' thành null
        if (in_array($preselectedClassId, ['all', 'none'])) {
            $preselectedClassId = null;
        }
        
        return view('admin.users.create', compact('classes', 'preselectedClassId'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,teacher,class_monitor,academic_sub_monitor',
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Gán role mặc định vào hệ thống Spatie
        $user->assignRole($validated['role']);

        // Gán classes dựa trên vai trò
        if ($validated['role'] === 'admin') {
            $user->classes()->detach();
        } elseif (in_array($validated['role'], ['class_monitor', 'academic_sub_monitor'])) {
            // Chỉ lấy lớp đầu tiên nếu là ban cán sự
            if (isset($validated['classes']) && !empty($validated['classes'])) {
                $user->classes()->sync([$validated['classes'][0]]);
            }
        } elseif (isset($validated['classes'])) {
            $user->classes()->sync($validated['classes']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $classes = ClassModel::orderBy('name')->get();
        $user->load(['roles', 'classes']);
        return view('admin.users.edit', compact('user', 'classes'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,teacher,class_monitor,academic_sub_monitor',
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Đồng bộ role vào hệ thống Spatie
        $user->syncRoles([$validated['role']]);

        // Gán classes dựa trên vai trò
        if ($validated['role'] === 'admin') {
            $user->classes()->detach();
        } elseif (in_array($validated['role'], ['class_monitor', 'academic_sub_monitor'])) {
            // Chỉ lấy lớp đầu tiên nếu là ban cán sự
            if (isset($validated['classes']) && !empty($validated['classes'])) {
                $user->classes()->sync([$validated['classes'][0]]);
            } else {
                $user->classes()->detach();
            }
        } elseif (isset($validated['classes'])) {
            $user->classes()->sync($validated['classes']);
        } else {
            $user->classes()->detach();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được cập nhật thành công.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được xóa thành công.');
    }
}

