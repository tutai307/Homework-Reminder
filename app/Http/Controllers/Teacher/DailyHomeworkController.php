<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Homework;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyHomeworkController extends Controller
{
    /**
     * Display form to select class and date.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Kiểm tra quyền tạo bài tập
        if (!$user->canCreateHomework()) {
            abort(403, 'Bạn không có quyền truy cập tính năng này.');
        }
        
        // Nếu là giáo viên hoặc lớp trưởng, tự động redirect đến lớp được gán
        if (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if ($class) {
                // Redirect đến trang list với lớp được gán và ngày hôm nay
                return redirect()->route('teacher.daily-homework.list', [
                    'class_id' => $class->id,
                    'date' => date('Y-m-d')
                ]);
            } else {
                return redirect()->back()
                    ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
            }
        }
        
        // Admin xem tất cả lớp
        $classes = ClassModel::orderBy('name')->get();
        return view('teacher.daily-homework.index', compact('classes'));
    }

    /**
     * Display homework list with calendar.
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền xem bài tập
        if (!$user->canCreateHomework()) {
            abort(403, 'Bạn không có quyền xem bài tập.');
        }

        // Nếu là giáo viên hoặc lớp trưởng, tự động lấy lớp được gán
        if (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if (!$class) {
                return redirect()->back()
                    ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
            }
        } else {
            // Admin cần chọn lớp
            $request->validate([
                'class_id' => 'required|exists:classes,id',
            ]);
            $class = ClassModel::findOrFail($request->class_id);
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($class->id)) {
            abort(403, 'Bạn không có quyền truy cập lớp này.');
        }
        $selectedDate = $request->date ?? date('Y-m-d');
        
        // Lấy bài tập cho ngày được chọn
        $homework = Homework::where('class_id', $class->id)
            ->where('date', $selectedDate)
            ->with(['items.subject', 'creator'])
            ->first();

        // Tính toán tuần hiện tại (Thứ 2 đến Chủ nhật)
        $selectedDateObj = \Carbon\Carbon::parse($selectedDate);
        $startOfWeek = $selectedDateObj->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek = $selectedDateObj->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        
        // Tạo mảng các ngày trong tuần
        $weekDays = [];
        $today = now()->format('Y-m-d');
        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $weekDays[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('d'),
                'dayName' => $date->format('l'),
                'dayNameVi' => $this->getDayNameVi($date->dayOfWeek),
                'isToday' => $date->format('Y-m-d') === $today,
                'isSelected' => $date->format('Y-m-d') === $selectedDate,
            ];
        }

        // Lấy danh sách bài tập trong tuần để hiển thị trên lịch
        $weekHomework = Homework::where('class_id', $class->id)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->get()
            ->pluck('date')
            ->toArray();

        return view('teacher.daily-homework.list', compact('class', 'homework', 'weekDays', 'selectedDate', 'weekHomework'));
    }

    /**
     * Get homework for a specific date (AJAX).
     */
    public function getHomework(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền xem bài tập
        if (!$user->canCreateHomework()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem bài tập.',
            ], 403);
        }
        
        // Nếu là giáo viên hoặc lớp trưởng, tự động lấy lớp được gán
        if (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if (!$class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa được gán lớp nào.',
                ], 403);
            }
            $classId = $class->id;
        } else {
            // Admin cần gửi class_id
            $request->validate([
                'class_id' => 'required|exists:classes,id',
            ]);
            $classId = $request->class_id;
            
            // Kiểm tra quyền truy cập lớp
            if (!$user->hasAccessToClass($classId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập lớp này.',
                ], 403);
            }
        }
        
        $request->validate([
            'date' => 'required|date',
        ]);

        $homework = Homework::where('class_id', $classId)
            ->where('date', $request->date)
            ->with(['items.subject', 'creator'])
            ->first();

        if ($homework) {
            return response()->json([
                'success' => true,
                'homework' => $homework,
                'items' => $homework->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'subject_name' => $item->subject->name,
                        'content' => $item->content,
                        'due_date' => $item->due_date ? $item->due_date->format('d/m/Y') : null,
                    ];
                }),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không có bài tập cho ngày này',
        ]);
    }

    /**
     * Get Zalo message format for a specific date.
     * Logic: Tìm tất cả các bài tập có hạn nộp là ngày hôm sau,
     * sau đó lấy ra các bài tập cần làm trong ngày hôm đó (ngày hôm sau).
     */
    public function getZaloMessage(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền xem bài tập
        if (!$user->canCreateHomework()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem bài tập.',
            ], 403);
        }
        
        // Nếu là giáo viên hoặc lớp trưởng, tự động lấy lớp được gán
        if (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if (!$class) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa được gán lớp nào.',
                ], 403);
            }
            $classId = $class->id;
        } else {
            // Admin cần gửi class_id
            $request->validate([
                'class_id' => 'required|exists:classes,id',
            ]);
            $classId = $request->class_id;
            
            // Kiểm tra quyền truy cập lớp
            if (!$user->hasAccessToClass($classId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập lớp này.',
                ], 403);
            }
        }
        
        $request->validate([
            'date' => 'required|date',
            'include_day_after_next' => 'nullable|boolean',
        ]);

        $selectedDate = \Carbon\Carbon::parse($request->date);
        $includeDayAfterNext = $request->boolean('include_day_after_next', false);
        
        // Ngày hiện tại (được chọn), hôm sau và hôm sau nữa
        $nextDate = $selectedDate->copy()->addDay();
        $nextDateStr = $nextDate->format('Y-m-d');
        
        $dayAfterNextDate = null;
        $dayAfterNextDateStr = null;
        if ($includeDayAfterNext) {
            $dayAfterNextDate = $selectedDate->copy()->addDays(2);
            $dayAfterNextDateStr = $dayAfterNextDate->format('Y-m-d');
        }
        
        // Lấy lớp để lấy thời khóa biểu
        $class = ClassModel::findOrFail($classId);

        // Build public share link (ưu tiên slug dễ đọc, fallback token)
        $slug = $class->ensurePublicShareSlug();
        $token = $class->ensurePublicShareToken();
        $portalUrl = url('/p/' . ($slug ?: $token));
        
        // Tìm tất cả các bài tập có hạn nộp là ngày hôm sau (không quan trọng homework.date là gì)
        // Ví dụ: Thứ 4 giao bài, hạn nộp thứ 6 → thứ 5 lấy tin nhắn sẽ thấy bài tập có hạn thứ 6
        $nextDayItems = \App\Models\HomeworkItem::whereHas('homework', function($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->where('due_date', $nextDateStr)
            ->with(['subject', 'homework'])
            ->get();
        
        // Lấy các bài tập cần làm trong ngày hôm sau (homework.date = ngày hôm sau)
        // Gộp với các bài tập có hạn nộp là ngày hôm sau
        $itemsToDoNextDay = \App\Models\HomeworkItem::whereHas('homework', function($query) use ($classId, $nextDateStr) {
                $query->where('class_id', $classId)
                      ->where('date', $nextDateStr);
            })
            ->with(['subject', 'homework'])
            ->get();
        
        // Gộp tất cả: bài tập có hạn nộp là ngày hôm sau + bài tập cần làm trong ngày hôm sau
        $nextDayItems = $nextDayItems->merge($itemsToDoNextDay)->unique('id');
        
        // Nếu có checkbox, cũng lấy bài tập có hạn nộp là ngày hôm sau nữa
        $dayAfterNextItems = collect();
        if ($includeDayAfterNext && $dayAfterNextDateStr) {
            // Tìm tất cả bài tập có hạn nộp là ngày hôm sau nữa
            $itemsWithDueDateDayAfterNext = \App\Models\HomeworkItem::whereHas('homework', function($query) use ($classId) {
                    $query->where('class_id', $classId);
                })
                ->where('due_date', $dayAfterNextDateStr)
                ->with(['subject', 'homework'])
                ->get();
            
            // Lấy các bài tập cần làm trong ngày hôm sau nữa (homework.date = ngày hôm sau nữa)
            $itemsToDoDayAfterNext = \App\Models\HomeworkItem::whereHas('homework', function($query) use ($classId, $dayAfterNextDateStr) {
                    $query->where('class_id', $classId)
                          ->where('date', $dayAfterNextDateStr);
                })
                ->with(['subject', 'homework'])
                ->get();
            
            // Gộp tất cả: bài tập có hạn nộp là ngày hôm sau nữa + bài tập cần làm trong ngày hôm sau nữa
            $dayAfterNextItems = $itemsWithDueDateDayAfterNext->merge($itemsToDoDayAfterNext)->unique('id');
        }

        // Ghi chú chung của ngày được chọn
        $homeworkSelected = Homework::where('class_id', $classId)
            ->where('date', $selectedDate->format('Y-m-d'))
            ->first();

        // Lấy homework (ghi chú chung) cho ngày hôm sau và hôm sau nữa
        $homeworkTomorrow = Homework::where('class_id', $classId)
            ->where('date', $nextDateStr)
            ->first();
        $homeworkDayAfter = null;
        if ($dayAfterNextDateStr) {
            $homeworkDayAfter = Homework::where('class_id', $classId)
                ->where('date', $dayAfterNextDateStr)
                ->first();
        }

        // Lấy thời khóa biểu để sắp xếp theo tiết
        $timetablesRaw = Timetable::where('class_id', $classId)
            ->with('subject')
            ->orderBy('weekday')
            ->orderBy('period')
            ->get();
        
        // Nhóm theo weekday và subject_id
        $timetables = [];
        foreach ($timetablesRaw as $timetable) {
            $weekday = $timetable->weekday;
            $subjectId = $timetable->subject_id;
            if (!isset($timetables[$weekday])) {
                $timetables[$weekday] = [];
            }
            if (!isset($timetables[$weekday][$subjectId])) {
                $timetables[$weekday][$subjectId] = collect();
            }
            $timetables[$weekday][$subjectId]->push($timetable);
        }

        // Format tin nhắn (kèm ghi chú chung nếu có)
        $message = $this->formatZaloMessageForUpcoming(
            $nextDayItems,
            $nextDate,
            $timetables,
            $dayAfterNextItems,
            $dayAfterNextDate,
            $portalUrl,
            $homeworkSelected?->notes,
            $homeworkTomorrow?->notes,
            $homeworkDayAfter?->notes,
            $selectedDate
        );

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Format message for Zalo - bài tập cần làm hôm sau và hôm sau nữa (nếu có).
     * Nhận vào collection của HomeworkItem thay vì Homework objects.
     */
    private function formatZaloMessageForUpcoming(
        $nextDayItems,
        $nextDate,
        $timetables,
        $dayAfterNextItems = null,
        $dayAfterNextDate = null,
        $portalUrl = null,
        $notesSelected = null,
        $notesTomorrow = null,
        $notesDayAfter = null,
        $selectedDate = null
    )
    {
        $today = now();
        $selectedDate = $selectedDate ?? $today;
        $nextDayNameVi = $this->getDayNameVi($nextDate->dayOfWeek);
        $nextFormattedDate = $nextDate->format('d/m/Y');
        
        $dayAfterNextDayNameVi = $dayAfterNextDate ? $this->getDayNameVi($dayAfterNextDate->dayOfWeek) : '';
        $dayAfterNextFormattedDate = $dayAfterNextDate ? $dayAfterNextDate->format('d/m/Y') : '';
        
        $message = "📚 BÀI TẬP CẦN LÀM\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        // Ghi chú chung của ngày được chọn (đặt lên đầu)
        if (!empty($notesSelected)) {
            $message .= "🗒️ Lời nhắc của GVCN / lớp trưởng:\n";
            $message .= "{$notesSelected}\n\n";
        }
        
        // Lọc các items có nội dung
        $nextDayItems = $nextDayItems->filter(function($item) {
            return !empty($item->content);
        });
        
        $dayAfterNextItems = $dayAfterNextItems ? $dayAfterNextItems->filter(function($item) {
            return !empty($item->content);
        }) : collect();
        
        // Gộp tất cả bài tập và sắp xếp
        $allItems = collect();
        
        // Thêm bài tập ngày hôm sau
        foreach ($nextDayItems as $item) {
            $allItems->push([
                'item' => $item,
                'date' => $nextDate,
                'date_label' => $nextFormattedDate . ' (' . $nextDayNameVi . ')',
                'due_date' => $item->due_date ? \Carbon\Carbon::parse($item->due_date) : null,
                'next_period' => $this->getNextPeriodForSubject($item->subject_id, $nextDate, $timetables),
            ]);
        }
        
        // Thêm bài tập ngày hôm sau nữa (nếu có)
        if ($dayAfterNextDate && $dayAfterNextItems->count() > 0) {
            foreach ($dayAfterNextItems as $item) {
                $allItems->push([
                    'item' => $item,
                    'date' => $dayAfterNextDate,
                    'date_label' => $dayAfterNextFormattedDate . ' (' . $dayAfterNextDayNameVi . ')',
                    'due_date' => $item->due_date ? \Carbon\Carbon::parse($item->due_date) : null,
                    'next_period' => $this->getNextPeriodForSubject($item->subject_id, $dayAfterNextDate, $timetables),
                ]);
            }
        }
        
        // Sắp xếp: ưu tiên deadline, sau đó theo tiết tiếp theo
        $sortedItems = $allItems->sortBy(function($data) use ($today) {
            // Nếu có deadline, sắp xếp theo deadline (sớm nhất trước)
            if ($data['due_date']) {
                return $data['due_date']->timestamp;
            }
            // Nếu không có deadline, sắp xếp theo tiết tiếp theo (tiết nhỏ nhất trước)
            // Nếu không có tiết, đặt cuối cùng
            return $data['next_period'] ?? 9999;
        });
        
        // Nhóm theo ngày
        $groupedByDate = $sortedItems->groupBy('date_label');
        
        // Hiển thị bài tập theo từng ngày
        foreach ($groupedByDate as $dateLabel => $items) {
            $message .= "📅 {$dateLabel}:\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
            
            if ($items->count() > 0) {
                foreach ($items as $data) {
                    $item = $data['item'];
                    $message .= "• {$item->subject->name}";
                    
                    // Hiển thị deadline nếu có
                    if ($data['due_date']) {
                        $dueDateStr = $data['due_date']->format('d/m/Y');
                        $message .= " (Hạn: {$dueDateStr})";
                    }
                    
                    $message .= "\n";
                    $message .= "  {$item->content}\n\n";
                }
            } else {
                $message .= "📝 Chưa có bài tập\n\n";
            }
            // Ghi chú chung (nếu có)
            if ($dateLabel === $nextFormattedDate . ' (' . $nextDayNameVi . ')' && !empty($notesTomorrow)) {
                $message .= "🗒️ Lời nhắc của GVCN / lớp trưởng:\n{$notesTomorrow}\n\n";
            }
            if ($dayAfterNextDate && $dateLabel === $dayAfterNextFormattedDate . ' (' . $dayAfterNextDayNameVi . ')' && !empty($notesDayAfter)) {
                $message .= "🗒️ Lời nhắc của GVCN / lớp trưởng:\n{$notesDayAfter}\n\n";
            }
        }
        
        // Nếu không có bài tập nào
        if ($sortedItems->count() == 0) {
            $message .= "📝 Chưa có bài tập cần làm trong 2 ngày tới.\n\n";
        }

        // Append public portal link so parents/students can follow on the website
        if (!empty($portalUrl)) {
            $message .= "\n🔗 Xem thời khoá biểu & bài tập trên web:\n";
            $message .= "{$portalUrl}\n";
        }
        
        return trim($message);
    }
    
    /**
     * Lấy tiết tiếp theo gần nhất của môn học trong thời khóa biểu.
     */
    private function getNextPeriodForSubject($subjectId, $date, $timetables)
    {
        $weekday = $date->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
        // Chuyển đổi: Carbon dayOfWeek (0=Sunday) -> DB weekday (1=Monday, 7=Sunday)
        $dbWeekday = $weekday == 0 ? 7 : $weekday;
        
        // Tìm môn học trong thời khóa biểu của ngày đó
        if (isset($timetables[$dbWeekday][$subjectId])) {
            $subjectTimetables = $timetables[$dbWeekday][$subjectId];
            // Lấy tiết đầu tiên (nhỏ nhất) của môn học trong ngày
            $periods = $subjectTimetables->pluck('period')->sort();
            return $periods->first();
        }
        
        // Nếu không tìm thấy trong ngày đó, tìm trong các ngày tiếp theo (trong tuần)
        // Tìm từ ngày hiện tại đến cuối tuần, sau đó từ đầu tuần
        for ($i = 1; $i <= 7; $i++) {
            $checkWeekday = ($dbWeekday + $i - 1) % 7 + 1;
            if ($checkWeekday == 0) $checkWeekday = 7;
            if (isset($timetables[$checkWeekday][$subjectId])) {
                $subjectTimetables = $timetables[$checkWeekday][$subjectId];
                $periods = $subjectTimetables->pluck('period')->sort();
                return $periods->first() + ($i * 100); // Thêm offset để phân biệt ngày
            }
        }
        
        return null;
    }

    /**
     * Get Vietnamese day name.
     * Carbon dayOfWeek: 0=Sunday, 1=Monday, ..., 6=Saturday
     */
    private function getDayNameVi($dayOfWeek)
    {
        $days = ['CN', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
        return $days[$dayOfWeek] ?? '';
    }

    /**
     * Tìm ngày của tiết học tiếp theo cho một môn học.
     * Trả về ngày của tiết học tiếp theo trong thời khóa biểu, bắt đầu từ ngày hôm sau.
     * Nếu không tìm thấy, trả về ngày hôm sau làm giá trị mặc định.
     */
    private function getNextPeriodDateForSubject($classId, $subjectId, $currentDate)
    {
        $currentDateObj = \Carbon\Carbon::parse($currentDate);
        
        // Tìm trong 7 ngày tiếp theo (1 tuần)
        for ($i = 1; $i <= 7; $i++) {
            $checkDate = $currentDateObj->copy()->addDays($i);
            $weekday = $checkDate->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
            // Chuyển đổi: Carbon dayOfWeek (0=Sunday) -> DB weekday (1=Monday, 7=Sunday)
            $dbWeekday = $weekday == 0 ? 7 : $weekday;
            
            // Kiểm tra xem môn học này có trong thời khóa biểu của ngày đó không
            $timetable = Timetable::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('weekday', $dbWeekday)
                ->first();
            
            if ($timetable) {
                return $checkDate->format('Y-m-d');
            }
        }
        
        // Nếu không tìm thấy trong 7 ngày, trả về ngày hôm sau làm giá trị mặc định
        return $currentDateObj->copy()->addDay()->format('Y-m-d');
    }

    /**
     * Show the form for creating daily homework.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền tạo bài tập
        if (!$user->canCreateHomework()) {
            abort(403, 'Bạn không có quyền tạo bài tập.');
        }
        
        // Nếu là giáo viên hoặc lớp trưởng, tự động lấy lớp được gán
        if (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if (!$class) {
                return redirect()->back()
                    ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
            }
            $date = $request->date ?? date('Y-m-d');
        } else {
            // Admin cần chọn lớp và ngày
            $request->validate([
                'class_id' => 'required|exists:classes,id',
                'date' => 'required|date',
            ]);
            $class = ClassModel::findOrFail($request->class_id);
            $date = $request->date;
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($class->id)) {
            abort(403, 'Bạn không có quyền truy cập lớp này.');
        }
        
        // Xác định thứ trong tuần (1=Thứ 2, 2=Thứ 3, ..., 6=Thứ 7, 7=Chủ nhật)
        $weekday = date('N', strtotime($date)); // 1=Monday, 7=Sunday
        
        // Lấy các môn học từ thời khóa biểu cho thứ đó
        $timetables = Timetable::where('class_id', $class->id)
            ->where('weekday', $weekday)
            ->with('subject')
            ->orderBy('period')
            ->get();

        // Kiểm tra xem đã có bài tập cho ngày này chưa
        $existingHomework = Homework::where('class_id', $class->id)
            ->where('date', $date)
            ->with('items')
            ->first();

        return view('teacher.daily-homework.create', compact('class', 'date', 'timetables', 'existingHomework'));
    }

    /**
     * Store a newly created homework.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền tạo bài tập
        if (!$user->canCreateHomework()) {
            abort(403, 'Bạn không có quyền tạo bài tập.');
        }
        
        // Nếu là giáo viên hoặc lớp trưởng, tự động lấy lớp được gán
        if (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if (!$class) {
                return redirect()->back()
                    ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
            }
            $classId = $class->id;
        } else {
            // Admin cần gửi class_id
            $request->validate([
                'class_id' => 'required|exists:classes,id',
            ]);
            $classId = $request->class_id;
        }
        
        $validated = $request->validate([
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'homework' => 'nullable|array',
            'homework.*.subject_id' => 'required_with:homework|exists:subjects,id',
            'homework.*.content' => 'nullable|string',
            'homework.*.due_date' => 'nullable|date',
        ]);
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($classId)) {
            abort(403, 'Bạn không có quyền truy cập lớp này.');
        }

        // Kiểm tra xem đã có bài tập cho ngày này chưa
        $existingHomework = Homework::where('class_id', $classId)
            ->where('date', $validated['date'])
            ->first();

        if ($existingHomework) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã có bài tập cho ngày này. Vui lòng chỉnh sửa bài tập hiện có.');
        }

        // Tạo bài tập mới
        $homework = Homework::create([
            'class_id' => $classId,
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Tạo các mục bài tập
        if (isset($validated['homework']) && is_array($validated['homework'])) {
            foreach ($validated['homework'] as $item) {
                if (!empty($item['content'])) {
                    // Nếu không có hạn nộp, tự động đặt là ngày của tiết học tiếp theo
                    $dueDate = $item['due_date'] ?? null;
                    if (!$dueDate) {
                        $nextPeriodDate = $this->getNextPeriodDateForSubject(
                            $classId,
                            $item['subject_id'],
                            $validated['date']
                        );
                        $dueDate = $nextPeriodDate;
                    }
                    
                    $homework->items()->create([
                        'subject_id' => $item['subject_id'],
                        'content' => $item['content'],
                        'due_date' => $dueDate,
                    ]);
                }
            }
        }

        // Redirect back to the calendar/list context so the user immediately sees the result + toast
        return redirect()->route('teacher.daily-homework.list', [
                'class_id' => $classId,
                'date' => $validated['date'],
            ])
            ->with('success', 'Giao bài tập thành công.');
    }

    /**
     * Show the form for editing homework.
     */
    public function edit(Homework $homework)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền tạo/chỉnh sửa bài tập
        if (!$user->canCreateHomework()) {
            abort(403, 'Bạn không có quyền chỉnh sửa bài tập.');
        }
        
        $class = $homework->classModel;

        // Không cho phép sửa bài tập ở quá khứ
        $today = now()->startOfDay();
        $homeworkDate = \Carbon\Carbon::parse($homework->date)->startOfDay();
        if ($homeworkDate->lt($today)) {
            return redirect()->route('teacher.daily-homework.list', [
                    'class_id' => $homework->class_id,
                    'date' => $homeworkDate->format('Y-m-d'),
                ])
                ->with('error', 'Không thể sửa bài tập của những ngày đã qua.');
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($class->id)) {
            abort(403, 'Bạn không có quyền truy cập lớp này.');
        }
        $date = $homework->date;
        
        // Xác định thứ trong tuần
        $weekday = date('N', strtotime($date));
        
        // Lấy các môn học từ thời khóa biểu
        $timetables = Timetable::where('class_id', $class->id)
            ->where('weekday', $weekday)
            ->with('subject')
            ->orderBy('period')
            ->get();

        // Lấy bài tập hiện có
        $homework->load('items');

        return view('teacher.daily-homework.edit', compact('homework', 'class', 'date', 'timetables'));
    }

    /**
     * Update the homework.
     */
    public function update(Request $request, Homework $homework)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền tạo/chỉnh sửa bài tập
        if (!$user->canCreateHomework()) {
            abort(403, 'Bạn không có quyền chỉnh sửa bài tập.');
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($homework->class_id)) {
            abort(403, 'Bạn không có quyền truy cập lớp này.');
        }

        // Không cho phép sửa bài tập ở quá khứ
        $today = now()->startOfDay();
        $homeworkDate = \Carbon\Carbon::parse($homework->date)->startOfDay();
        if ($homeworkDate->lt($today)) {
            return redirect()->route('teacher.daily-homework.list', [
                    'class_id' => $homework->class_id,
                    'date' => $homeworkDate->format('Y-m-d'),
                ])
                ->with('error', 'Không thể sửa bài tập của những ngày đã qua.');
        }
        
        // Note: the form submits one "homework[item]" per timetable slot, even if content is empty.
        // So `content` must be nullable; we'll only persist items that actually have content.
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'homework' => 'nullable|array',
            'homework.*.subject_id' => 'required_with:homework|exists:subjects,id',
            'homework.*.content' => 'nullable|string',
            'homework.*.due_date' => 'nullable|date',
        ]);

        // Cập nhật ghi chú
        $homework->update([
            'notes' => $validated['notes'] ?? null,
        ]);

        // Xóa các mục bài tập cũ
        $homework->items()->delete();

        // Tạo lại các mục bài tập
        if (isset($validated['homework']) && is_array($validated['homework'])) {
            foreach ($validated['homework'] as $item) {
                if (!empty($item['content'])) {
                    // Nếu không có hạn nộp, tự động đặt là ngày của tiết học tiếp theo
                    $dueDate = $item['due_date'] ?? null;
                    if (!$dueDate) {
                        $nextPeriodDate = $this->getNextPeriodDateForSubject(
                            $homework->class_id,
                            $item['subject_id'],
                            $homework->date->format('Y-m-d')
                        );
                        $dueDate = $nextPeriodDate;
                    }
                    
                    $homework->items()->create([
                        'subject_id' => $item['subject_id'],
                        'content' => $item['content'],
                        'due_date' => $dueDate,
                    ]);
                }
            }
        }

        // Redirect back to the calendar/list context so the user immediately sees the updated result + toast
        return redirect()->route('teacher.daily-homework.list', [
                'class_id' => $homework->class_id,
                'date' => $homework->date->format('Y-m-d'),
            ])
            ->with('success', 'Sửa bài tập thành công.');
    }

    /**
     * Delete homework (only allowed for today's homework).
     */
    public function destroy(Request $request, Homework $homework)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền xóa bài tập
        if (!$user->canCreateHomework()) {
            abort(403, 'Bạn không có quyền xóa bài tập.');
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($homework->class_id)) {
            abort(403, 'Bạn không có quyền truy cập lớp này.');
        }
        
        // Chỉ cho phép xóa nếu là ngày hôm nay
        $today = now()->startOfDay();
        $homeworkDate = \Carbon\Carbon::parse($homework->date)->startOfDay();
        
        if (!$homeworkDate->isSameDay($today)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể xóa bài tập của ngày hôm nay.',
                ], 403);
            }
            return redirect()->back()
                ->with('error', 'Chỉ có thể xóa bài tập của ngày hôm nay.');
        }
        
        // Keep context for redirect after deletion
        $classId = $homework->class_id;
        $date = \Carbon\Carbon::parse($homework->date)->format('Y-m-d');

        // Xóa bài tập
        $homework->delete();
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bài tập đã được xóa thành công.',
            ]);
        }
        
        // Redirect back to the calendar/list context so the user stays on the same screen + toast
        return redirect()->route('teacher.daily-homework.list', [
                'class_id' => $classId,
                'date' => $date,
            ])
            ->with('success', 'Xóa bài tập thành công.');
    }
}

