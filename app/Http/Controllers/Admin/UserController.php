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
    public function index()
    {
        $users = User::with(['roles', 'classes'])->latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $classes = ClassModel::orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'classes'));
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
            'role' => 'required|in:admin,teacher,class_monitor',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Gán roles
        if (isset($validated['roles']) && !empty($validated['roles'])) {
            // Chuyển đổi IDs thành Role models
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);
        } else {
            // Nếu không chọn roles, gán role mặc định từ field 'role'
            $user->assignRole($validated['role']);
        }

        // Gán classes
        if (isset($validated['classes'])) {
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
        $roles = Role::orderBy('name')->get();
        $classes = ClassModel::orderBy('name')->get();
        $user->load(['roles', 'classes']);
        return view('admin.users.edit', compact('user', 'roles', 'classes'));
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
            'role' => 'required|in:admin,teacher,class_monitor',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
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

        // Gán roles
        if (isset($validated['roles']) && !empty($validated['roles'])) {
            // Chuyển đổi IDs thành Role models
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);
        } else {
            // Nếu không chọn roles, chỉ gán role mặc định từ field 'role'
            $user->syncRoles([]);
            $user->assignRole($validated['role']);
        }

        // Gán classes
        if (isset($validated['classes'])) {
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

