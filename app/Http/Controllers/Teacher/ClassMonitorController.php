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
     * Display a listing of class monitors.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Chỉ giáo viên mới truy cập được
        if (!$user->isTeacher()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Lấy lớp được gán cho giáo viên (chỉ 1 lớp)
        $class = $user->getAssignedClass();
        
        if (!$class) {
            return redirect()->route('teacher.timetables.index')
                ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
        }
        
        // Lấy lớp trưởng của lớp này (nếu có)
        $classMonitor = User::whereHas('classes', function($query) use ($class) {
            $query->where('classes.id', $class->id);
        })
        ->where('role', 'class_monitor')
        ->first();
        
        return view('teacher.class-monitor.index', compact('class', 'classMonitor'));
    }

    /**
     * Show the form for creating a class monitor.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Chỉ giáo viên mới truy cập được
        if (!$user->isTeacher()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Lấy lớp được gán cho giáo viên
        $class = $user->getAssignedClass();
        
        if (!$class) {
            return redirect()->route('teacher.timetables.index')
                ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
        }
        
        // Kiểm tra xem đã có lớp trưởng chưa
        $existingMonitor = User::whereHas('classes', function($query) use ($class) {
            $query->where('classes.id', $class->id);
        })
        ->where('role', 'class_monitor')
        ->first();
        
        if ($existingMonitor) {
            return redirect()->route('teacher.class-monitor.index')
                ->with('error', 'Lớp này đã có lớp trưởng. Vui lòng xóa lớp trưởng hiện tại trước khi tạo mới.');
        }
        
        return view('teacher.class-monitor.create', compact('class'));
    }

    /**
     * Store a newly created class monitor.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Chỉ giáo viên mới truy cập được
        if (!$user->isTeacher()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Lấy lớp được gán cho giáo viên
        $class = $user->getAssignedClass();
        
        if (!$class) {
            return redirect()->route('teacher.timetables.index')
                ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
        }
        
        // Kiểm tra xem đã có lớp trưởng chưa
        $existingMonitor = User::whereHas('classes', function($query) use ($class) {
            $query->where('classes.id', $class->id);
        })
        ->where('role', 'class_monitor')
        ->first();
        
        if ($existingMonitor) {
            return redirect()->route('teacher.class-monitor.index')
                ->with('error', 'Lớp này đã có lớp trưởng.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Tạo lớp trưởng
        $classMonitor = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'class_monitor',
        ]);
        
        // Gán role
        $classMonitorRole = Role::where('name', 'class_monitor')->first();
        if ($classMonitorRole) {
            $classMonitor->assignRole($classMonitorRole);
        }
        
        // Gán lớp cho lớp trưởng
        $classMonitor->classes()->attach($class->id);
        
        return redirect()->route('teacher.class-monitor.index')
            ->with('success', 'Lớp trưởng đã được tạo thành công.');
    }

    /**
     * Remove the specified class monitor.
     */
    public function destroy(User $classMonitor)
    {
        $user = Auth::user();
        
        // Chỉ giáo viên mới truy cập được
        if (!$user->isTeacher()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Kiểm tra xem lớp trưởng có thuộc lớp của giáo viên không
        $class = $user->getAssignedClass();
        
        if (!$class) {
            abort(403, 'Bạn chưa được gán lớp nào.');
        }
        
        if (!$classMonitor->classes()->where('classes.id', $class->id)->exists()) {
            abort(403, 'Lớp trưởng này không thuộc lớp của bạn.');
        }
        
        if ($classMonitor->role !== 'class_monitor') {
            abort(403, 'Người dùng này không phải là lớp trưởng.');
        }
        
        // Xóa lớp trưởng
        $classMonitor->classes()->detach($class->id);
        $classMonitor->delete();
        
        return redirect()->route('teacher.class-monitor.index')
            ->with('success', 'Lớp trưởng đã được xóa thành công.');
    }
}

