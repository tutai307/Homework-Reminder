<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ClassMonitorController extends Controller
{
    /**
     * Display a listing of classes or the management team of a specific class.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Chỉ giáo viên mới truy cập được
        if (!$user->isTeacher()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }

        $classes = $user->classes()->orderBy('name')->get();

        if ($classes->isEmpty()) {
            return redirect()->route('teacher.timetables.index')
                ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
        }

        $classId = $request->get('class_id');

        // Nếu giáo viên có nhiều lớp và chưa chọn lớp, hiển thị trang chọn lớp
        if ($classes->count() > 1 && !$classId) {
            return view('teacher.class-monitor.select-class', compact('classes'));
        }

        // Tự động lấy lớp đầu tiên nếu chỉ có 1 lớp và chưa có classId
        if (!$classId) {
            $classId = $classes->first()->id;
        }

        $class = ClassModel::findOrFail($classId);

        // Kiểm tra xem giáo viên có quyền quản lý lớp này không
        if (!$user->hasAccessToClass($classId)) {
            abort(403, 'Bạn không có quyền quản lý Ban cán sự lớp này.');
        }
        
        // Lấy danh sách ban cán sự của lớp này
        $managementTeam = User::whereHas('classes', function($query) use ($classId) {
            $query->where('classes.id', $classId);
        })
        ->whereIn('role', ['class_monitor', 'academic_sub_monitor'])
        ->get();
        
        $classMonitor = $managementTeam->where('role', 'class_monitor')->first();
        $academicSubMonitor = $managementTeam->where('role', 'academic_sub_monitor')->first();
        
        return view('teacher.class-monitor.index', compact('class', 'classMonitor', 'academicSubMonitor', 'classes'));
    }

    /**
     * Show the form for creating a member of the management team.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $role = $request->get('role', 'class_monitor');
        
        // Validate role
        if (!in_array($role, ['class_monitor', 'academic_sub_monitor'])) {
            $role = 'class_monitor';
        }
        
        // Chỉ giáo viên mới truy cập được
        if (!$user->isTeacher()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Lấy lớp từ request
        $classId = $request->get('class_id');
        if (!$classId) {
            return redirect()->route('teacher.class-monitor.index')
                ->with('error', 'Vui lòng chọn lớp trước.');
        }

        $class = ClassModel::findOrFail($classId);
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($classId)) {
            abort(403, 'Bạn không có quyền quản lý Ban cán sự lớp này.');
        }
        
        // Kiểm tra xem đã có người ở vị trí này chưa
        $existing = User::whereHas('classes', function($query) use ($classId) {
            $query->where('classes.id', $classId);
        })
        ->where('role', $role)
        ->first();
        
        if ($existing) {
            $roleName = $role === 'class_monitor' ? 'Lớp trưởng' : 'Lớp phó học tập';
            return redirect()->route('teacher.class-monitor.index', ['class_id' => $classId])
                ->with('error', "Lớp này đã có {$roleName}. Vui lòng xóa người hiện tại trước khi tạo mới.");
        }
        
        return view('teacher.class-monitor.create', compact('class', 'role'));
    }

    /**
     * Store a newly created management team member.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Chỉ giáo viên mới truy cập được
        if (!$user->isTeacher()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Lấy lớp từ request
        $classId = $request->get('class_id');
        if (!$classId) {
            return redirect()->route('teacher.class-monitor.index')
                ->with('error', 'Vui lòng chọn lớp.');
        }

        $class = ClassModel::findOrFail($classId);
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($classId)) {
            abort(403, 'Bạn không có quyền quản lý Ban cán sự lớp này.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:class_monitor,academic_sub_monitor',
            'class_id' => 'required|exists:classes,id',
        ]);
        
        // Kiểm tra xem đã có người ở vị trí này chưa
        $existing = User::whereHas('classes', function($query) use ($classId) {
            $query->where('classes.id', $classId);
        })
        ->where('role', $validated['role'])
        ->first();
        
        if ($existing) {
            $roleName = $validated['role'] === 'class_monitor' ? 'Lớp trưởng' : 'Lớp phó học tập';
            return redirect()->route('teacher.class-monitor.index', ['class_id' => $classId])
                ->with('error', "Lớp này đã có {$roleName}.");
        }
        
        // Tạo tài khoản
        $newMember = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);
        
        // Gán role
        $roleModel = Role::where('name', $validated['role'])->first();
        if ($roleModel) {
            $newMember->assignRole($roleModel);
        }
        
        // Gán lớp
        $newMember->classes()->attach($class->id);
        
        $roleName = $validated['role'] === 'class_monitor' ? 'Lớp trưởng' : 'Lớp phó học tập';
        return redirect()->route('teacher.class-monitor.index', ['class_id' => $classId])
            ->with('success', "{$roleName} đã được tạo thành công.");
    }

    /**
     * Remove the specified management team member.
     */
    public function destroy(User $classMonitor) // $classMonitor variable name kept for compatibility but it's any member
    {
        $user = Auth::user();
        
        // Chỉ giáo viên mới truy cập được
        if (!$user->isTeacher()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Kiểm tra xem người dùng có thuộc lớp của giáo viên không
        $class = $user->getAssignedClass();
        
        if (!$class) {
            abort(403, 'Bạn chưa được gán lớp nào.');
        }
        
        if (!$classMonitor->classes()->where('classes.id', $class->id)->exists()) {
            abort(403, 'Người dùng này không thuộc lớp của bạn.');
        }
        
        if (!in_array($classMonitor->role, ['class_monitor', 'academic_sub_monitor'])) {
            abort(403, 'Người dùng này không thuộc ban cán sự lớp.');
        }
        
        $roleName = $classMonitor->role === 'class_monitor' ? 'Lớp trưởng' : 'Lớp phó học tập';
        $classId = $classMonitor->classes()->first()->id;
        
        // Xóa
        $classMonitor->classes()->detach($classId);
        $classMonitor->delete();
        
        return redirect()->route('teacher.class-monitor.index', ['class_id' => $classId])
            ->with('success', "{$roleName} đã được xóa thành công.");
    }
}

