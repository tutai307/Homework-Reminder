<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Homework;
use App\Models\Timetable;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {
        $today = now()->format('Y-m-d');
        $selectedDate = $request->date ? \Carbon\Carbon::parse($request->date)->format('Y-m-d') : $today;
        $selectedDateObj = \Carbon\Carbon::parse($selectedDate);
        
        $weekday = $selectedDateObj->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
        // Chuyển đổi: Carbon dayOfWeek (0=Sunday) -> DB weekday (1=Monday, 7=Sunday)
        $dbWeekday = $weekday == 0 ? 7 : $weekday;

        // 1. Thẻ thống kê
        $totalClasses = ClassModel::count();
        
        // Lấy danh sách lớp đã có bài tập trong ngày được chọn
        $classesWithHomework = Homework::where('date', $selectedDate)
            ->distinct()
            ->pluck('class_id')
            ->toArray();
        
        $classesWithHomeworkCount = count($classesWithHomework);
        $classesWithoutHomeworkCount = $totalClasses - $classesWithHomeworkCount;
        
        // Tổng số bài tập được tạo trong ngày (tổng số homework items)
        $totalHomeworkItemsToday = DB::table('homework_items')
            ->join('homework', 'homework_items.homework_id', '=', 'homework.id')
            ->where('homework.date', $selectedDate)
            ->count();

        // 3. Bài tập theo thời khóa biểu
        // Tổng số môn học dự kiến của tất cả các lớp hôm nay
        // Tính số môn học duy nhất cho mỗi lớp, sau đó tổng hợp
        $expectedSubjectsByClass = DB::table('timetables')
            ->where('weekday', $dbWeekday)
            ->select('class_id', 'subject_id')
            ->distinct()
            ->get()
            ->groupBy('class_id')
            ->map(function($items) {
                return $items->pluck('subject_id')->unique()->count();
            });
        
        $totalExpectedSubjects = $expectedSubjectsByClass->sum();

        // Tổng số môn học đã tạo bài tập (chỉ tính các môn có nội dung)
        // Tính số môn học duy nhất cho mỗi lớp, sau đó tổng hợp
        $subjectsWithHomeworkByClass = DB::table('homework_items')
            ->join('homework', 'homework_items.homework_id', '=', 'homework.id')
            ->where('homework.date', $selectedDate)
            ->whereNotNull('homework_items.content')
            ->where('homework_items.content', '!=', '')
            ->select('homework_items.subject_id', 'homework.class_id')
            ->distinct()
            ->get()
            ->groupBy('class_id')
            ->map(function($items) {
                return $items->pluck('subject_id')->unique()->count();
            });
        
        $totalSubjectsWithHomework = $subjectsWithHomeworkByClass->sum();

        // Tỷ lệ bao phủ
        $coverageRate = $totalExpectedSubjects > 0 
            ? round(($totalSubjectsWithHomework / $totalExpectedSubjects) * 100, 2) 
            : 0;

        // 2. Bảng tình trạng bài tập của lớp
        $classes = ClassModel::orderBy('name')->get();
        
        // Lấy tất cả homework ngày đang chọn với items
        $homeworksToday = Homework::where('date', $selectedDate)
            ->with('items')
            ->get()
            ->keyBy('class_id');

        $classesStatus = $classes->map(function($class) use ($homeworksToday, $expectedSubjectsByClass, $subjectsWithHomeworkByClass) {
            $homework = $homeworksToday->get($class->id);
            $hasHomework = $homework ? true : false;
            
            $expected = $expectedSubjectsByClass->get($class->id, 0);
            $actual = $subjectsWithHomeworkByClass->get($class->id, 0);
            
            $rate = $expected > 0 ? round(($actual / $expected) * 100, 0) : 0;
            
            return [
                'id' => $class->id,
                'name' => $class->name,
                'school_year' => $class->school_year,
                'has_homework' => $hasHomework,
                'subject_count' => $actual,
                'expected_count' => $expected,
                'coverage_rate' => $rate
            ];
        });

        // 4. Dữ liệu cho biểu đồ (Charts)
        
        // Biểu đồ 1: Tỷ lệ bao phủ 7 ngày gần nhất
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->format('d/m');
            
            // Expected subjects for this day of week
            $wDay = now()->subDays($i)->dayOfWeek;
            $dbWDay = $wDay == 0 ? 7 : $wDay;
            
            $expectedCount = DB::table('timetables')
                ->where('weekday', $dbWDay)
                ->distinct('class_id', 'subject_id')
                ->count();
            
            $actualCount = DB::table('homework_items')
                ->join('homework', 'homework_items.homework_id', '=', 'homework.id')
                ->where('homework.date', $date)
                ->whereNotNull('homework_items.content')
                ->where('homework_items.content', '!=', '')
                ->distinct('homework_items.subject_id', 'homework.class_id')
                ->count();
                
            $rate = $expectedCount > 0 ? round(($actualCount / $expectedCount) * 100, 1) : 0;
            
            $last7Days->push([
                'date' => $date, // Required for interaction
                'label' => $dayName,
                'rate' => $rate,
                'actual' => $actualCount,
                'expected' => $expectedCount
            ]);
        }
        
        // Biểu đồ 2: Phân bố bài tập theo môn học
        $subjectDistribution = DB::table('homework_items')
            ->join('homework', 'homework_items.homework_id', '=', 'homework.id')
            ->join('subjects', 'homework_items.subject_id', '=', 'subjects.id')
            ->where('homework.date', $selectedDate)
            ->whereNotNull('homework_items.content')
            ->where('homework_items.content', '!=', '')
            ->select('subjects.name', DB::raw('count(*) as total'))
            ->groupBy('subjects.name')
            ->get();

        // 5. Nhật ký hoạt động gần nhất
        $recentLogs = ActivityLog::with(['user', 'class'])
            ->latest()
            ->limit(10)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'tableHtml' => view('admin.dashboard.partials.class_status_table', compact('classesStatus', 'selectedDate'))->render(),
                'subjectDistribution' => $subjectDistribution,
                'selectedDateTitle' => \Carbon\Carbon::parse($selectedDate)->format('d/m'),
                'isToday' => $selectedDate === $today,
                
                // Add stat values for quick update
                'coverageRate' => $coverageRate,
                'totalSubjectsWithHomework' => $totalSubjectsWithHomework,
                'totalExpectedSubjects' => $totalExpectedSubjects,
                'totalHomeworkItemsToday' => $totalHomeworkItemsToday,
                'classesWithoutHomeworkCount' => $classesWithoutHomeworkCount,
            ]);
        }

        return view('admin.dashboard.index', compact(
            'totalClasses',
            'classesWithHomeworkCount',
            'classesWithoutHomeworkCount',
            'totalHomeworkItemsToday',
            'classesStatus',
            'totalExpectedSubjects',
            'totalSubjectsWithHomework',
            'coverageRate',
            'selectedDate',
            'today',
            'last7Days',
            'subjectDistribution',
            'recentLogs'
        ));
    }

    public function classHomework(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date'
        ]);

        $class = ClassModel::findOrFail($request->class_id);
        $date = $request->date;

        $homework = Homework::with(['items.subject'])
            ->where('class_id', $class->id)
            ->where('date', $date)
            ->first();

        return response()->json([
            'html' => view('admin.dashboard.partials.class_homework_modal_content', compact('class', 'date', 'homework'))->render(),
            'title' => 'Bài tập lớp ' . $class->name . ' - ' . \Carbon\Carbon::parse($date)->format('d/m/Y')
        ]);
    }
}

