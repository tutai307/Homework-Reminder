<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimetableController extends Controller
{
    /**
     * Display a listing of timetables (select class).
     */
    public function index()
    {
        $user = Auth::user();
        
        // Kiểm tra quyền tạo thời khóa biểu
        if (!$user->canCreateTimetable()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Admin xem tất cả lớp, teacher chỉ xem lớp được gán
        if ($user->isAdmin()) {
            $classes = ClassModel::orderBy('name')->get();
        } else {
            $classes = $user->classes()->orderBy('name')->get();
        }
        
        return view('teacher.timetables.index', compact('classes'));
    }

    /**
     * Show the form for creating/editing timetable for a class.
     */
    public function create(ClassModel $class)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền tạo thời khóa biểu
        if (!$user->canCreateTimetable()) {
            abort(403, 'Bạn không có quyền tạo thời khóa biểu.');
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($class->id)) {
            abort(403, 'Bạn không có quyền truy cập lớp này.');
        }
        
        $subjects = Subject::orderBy('name')->get();
        
        // Lấy thời khóa biểu hiện tại và tổ chức theo weekday và period
        $existingTimetables = Timetable::where('class_id', $class->id)->get();
        $timetables = [];
        
        foreach ($existingTimetables as $timetable) {
            $timetables[$timetable->weekday][$timetable->period] = $timetable->subject_id;
        }

        return view('teacher.timetables.create', compact('class', 'subjects', 'timetables'));
    }

    /**
     * Store/Update timetable for a class.
     */
    public function store(Request $request, ClassModel $class)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền tạo thời khóa biểu
        if (!$user->canCreateTimetable()) {
            abort(403, 'Bạn không có quyền tạo thời khóa biểu.');
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($class->id)) {
            abort(403, 'Bạn không có quyền truy cập lớp này.');
        }
        
        $request->validate([
            'timetable' => 'required|array',
            'timetable.*.*' => 'nullable|exists:subjects,id',
        ]);

        try {
            \DB::beginTransaction();

            // Xóa thời khóa biểu cũ của lớp
            Timetable::where('class_id', $class->id)->delete();

            // Lưu thời khóa biểu mới
            if ($request->has('timetable')) {
                foreach ($request->timetable as $weekday => $periods) {
                    foreach ($periods as $period => $subjectId) {
                        if ($subjectId) {
                            Timetable::create([
                                'class_id' => $class->id,
                                'weekday' => (int)$weekday,
                                'subject_id' => (int)$subjectId,
                                'period' => (int)$period,
                            ]);
                        }
                    }
                }
            }

            \DB::commit();

            return redirect()->route('teacher.timetables.index')
                ->with('success', 'Thời khóa biểu đã được lưu thành công.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Lỗi khi lưu thời khóa biểu: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã xảy ra lỗi khi lưu thời khóa biểu. Vui lòng thử lại. ' . (config('app.debug') ? $e->getMessage() : ''));
        }
    }
}

