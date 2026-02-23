<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Homework;
use App\Models\HomeworkItem;
use App\Models\Timetable;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyHomeworkController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }
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
        
        // Nếu là Admin, lấy tất cả lớp. Nếu là Giáo viên/Lớp trưởng/Lớp phó, lấy các lớp được gán.
        if ($user->isAdmin()) {
            $classes = ClassModel::orderBy('name')->get();
        } else {
            $classes = $user->classes()->orderBy('name')->get();
            
            if ($classes->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
            }

            // Tự động chuyển hướng nếu chỉ có 1 lớp (dành cho Lớp phó học tập/Lớp trưởng)
            if ($classes->count() === 1 && ($user->isClassMonitor() || $user->isAcademicSubMonitor())) {
                return redirect()->route('teacher.daily-homework.list', ['class_id' => $classes->first()->id]);
            }
        }
        
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

        // Xác định lớp cần xem
        if ($request->has('class_id')) {
            $classId = $request->class_id;
            $class = ClassModel::findOrFail($classId);
        } elseif (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if (!$class) {
                return redirect()->back()
                    ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
            }
            $classId = $class->id;
        } else {
            // Admin bắt buộc phải chọn lớp
            return redirect()->route('teacher.daily-homework.index')
                ->with('error', 'Vui lòng chọn một lớp để xem bài tập.');
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
        
        // Xác định classId
        if ($request->has('class_id')) {
            $classId = $request->class_id;
        } elseif (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if (!$class) {
                return response()->json(['success' => false, 'message' => 'Bạn chưa được gán lớp nào.'], 403);
            }
            $classId = $class->id;
        } else {
            return response()->json(['success' => false, 'message' => 'Vui lòng cung cấp ID lớp học.'], 400);
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($classId)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập lớp này.'], 403);
        }
        
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date; // Use a local variable for date

        $homework = Homework::where('class_id', $classId)
            ->where('date', $date)
            ->with('items.subject')
            ->first();

        // Lấy thêm các bài tập ĐẾN HẠN vào ngày này (từ các ngày giao trước đó)
        $dueItems = HomeworkItem::whereHas('homework', function($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->whereDate('due_date', $date)
            ->with('subject')
            ->get();

        if ($homework || $dueItems->count() > 0) {
            $itemsFromHomework = $homework ? $homework->items : collect();
            
            // 1. Danh sách bài tập được GIAO TRONG NGÀY (assigned_items)
            $assignedGroups = $itemsFromHomework->groupBy('subject_id')->map(function ($group) {
                $first = $group->first();
                return [
                    'subject_name' => $first->subject->name,
                    'content' => $group->pluck('content')->unique()->implode("\n"),
                    'due_date' => $first->due_date ? $first->due_date->format('d/m/Y') : null,
                    'is_due_today' => false, // Giao mới thì không tính là "đến hạn nộp" trong ngữ cảnh này
                ];
            })->values();

            // 2. Danh sách tổng hợp dùng cho PREVIEW (preview_items)
            $allItems = $itemsFromHomework->concat($dueItems);
            $previewGroups = $allItems->groupBy('subject_id')->map(function ($group) use ($date) {
                $first = $group->first();
                $isDueToday = $group->contains(function($item) use ($date) {
                    return $item->due_date && $item->due_date->format('Y-m-d') === $date;
                });

                return [
                    'subject_name' => $first->subject->name,
                    'content' => $group->pluck('content')->unique()->implode("\n"),
                    'due_date' => $first->due_date ? $first->due_date->format('d/m/Y') : null,
                    'is_due_today' => $isDueToday,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'homework' => $homework, // Có thể null
                'items' => $assignedGroups, // Trang chính chỉ hiện bài tập được giao
                'assigned_items' => $assignedGroups,
                'preview_items' => $previewGroups, // Modal Preview hiện tất cả
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
        
        // Xác định classId
        if ($request->has('class_id')) {
            $classId = $request->class_id;
        } elseif (!$user->isAdmin()) {
            $class = $user->getAssignedClass();
            if (!$class) {
                return response()->json(['success' => false, 'message' => 'Bạn chưa được gán lớp nào.'], 403);
            }
            $classId = $class->id;
        } else {
            return response()->json(['success' => false, 'message' => 'Vui lòng cung cấp ID lớp học.'], 400);
        }
        
        // Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($classId)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập lớp này.'], 403);
        }
        
        $request->validate([
            'date' => 'required|date',
            'include_day_after_next' => 'nullable|boolean',
        ]);

        $selectedDate = \Carbon\Carbon::parse($request->date);
        $includeDayAfterNext = $request->boolean('include_day_after_next', false);
        
        // Nếu chọn ngày hôm nay -> lấy bài tập cho ngày mai
        // Nếu chọn ngày khác (trong tương lai/quá khứ) -> lấy bài tập cho chính ngày đó
        if ($selectedDate->isToday()) {
            $nextDate = $selectedDate->copy()->addDay();
        } else {
            $nextDate = $selectedDate->copy();
        }
        $nextDateStr = $nextDate->format('Y-m-d');
        
        $dayAfterNextDate = null;
        $dayAfterNextDateStr = null;
        if ($includeDayAfterNext) {
            $dayAfterNextDate = $nextDate->copy()->addDay();
            $dayAfterNextDateStr = $dayAfterNextDate->format('Y-m-d');
        }
        
        // Lấy lớp để lấy thời khóa biểu
        $class = ClassModel::findOrFail($classId);

        // Build public share link (ưu tiên slug dễ đọc, fallback token)
        $slug = $class->ensurePublicShareSlug();
        $token = $class->ensurePublicShareToken();
        $portalUrl = url('/p/' . ($slug ?: $token));
        
        // Lấy bài tập cho các ngày liên quan (ngày tiếp theo và ngày sau nữa nếu có)
        $datesToFetch = [$nextDateStr];
        if ($includeDayAfterNext && $dayAfterNextDateStr) {
            $datesToFetch[] = $dayAfterNextDateStr;
        }

        // Lấy tất cả bài tập liên quan trong một lần query để giảm round-trip DB
        $relatedItems = \App\Models\HomeworkItem::whereHas('homework', function($query) use ($classId, $datesToFetch) {
                $query->where('class_id', $classId);
            })
            ->where(function($query) use ($datesToFetch) {
                $query->whereIn('due_date', $datesToFetch)
                      ->orWhereHas('homework', function($q) use ($datesToFetch) {
                          $q->whereIn('date', $datesToFetch);
                      });
            })
            ->with(['subject', 'homework'])
            ->get();

        // Phân loại lại theo ngày
        $nextDayItems = $relatedItems->filter(function($item) use ($nextDateStr) {
            return $item->due_date?->format('Y-m-d') === $nextDateStr || $item->homework->date === $nextDateStr;
        })->unique('id');

        $dayAfterNextItems = collect();
        if ($includeDayAfterNext && $dayAfterNextDateStr) {
            $dayAfterNextItems = $relatedItems->filter(function($item) use ($dayAfterNextDateStr) {
                return $item->due_date?->format('Y-m-d') === $dayAfterNextDateStr || $item->homework->date === $dayAfterNextDateStr;
            })->unique('id');
        }

        // Ghi chú chung của các ngày
        $homeworkNotes = Homework::where('class_id', $classId)
            ->whereIn('date', array_merge([$selectedDate->format('Y-m-d')], $datesToFetch))
            ->get()
            ->keyBy('date');

        $homeworkSelected = $homeworkNotes->get($selectedDate->format('Y-m-d'));
        $homeworkTomorrow = $homeworkNotes->get($nextDateStr);
        $homeworkDayAfter = $dayAfterNextDateStr ? $homeworkNotes->get($dayAfterNextDateStr) : null;

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

        // Lấy danh sách ID các môn học của ngày mai để nhắc làm sớm
        $nextDayWeekday = $nextDate->dayOfWeek == 0 ? 7 : $nextDate->dayOfWeek;
        $nextDaySubjectIds = Timetable::where('class_id', $classId)
            ->where('weekday', $nextDayWeekday)
            ->pluck('subject_id')
            ->unique()
            ->toArray();

        // Lấy các bài tập của các môn ngày mai nhưng hạn nộp sau ngày mai
        $earlyRemindItems = \App\Models\HomeworkItem::whereHas('homework', function($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->whereIn('subject_id', $nextDaySubjectIds)
            ->where('due_date', '>', $nextDateStr)
            ->with(['subject', 'homework'])
            ->get()
            ->unique('id');

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
            $selectedDate,
            $earlyRemindItems
        );

        // --- BỔ SUNG: AI TẠO KẾ HOẠCH HỌC TẬP ---
        $allHomeworkItems = collect()
            ->concat($nextDayItems)
            ->concat($dayAfterNextItems)
            ->concat($earlyRemindItems)
            ->filter();

        if ($allHomeworkItems->count() > 0) {
            $formattedData = $allHomeworkItems->map(function($item) {
                return [
                    'subject' => $item->subject?->name,
                    'content' => $item->content,
                    'due_date' => $item->due_date ? $item->due_date->format('Y-m-d') : null,
                ];
            })->values()->toArray();

            $studyPlan = $this->aiService->generateStudyPlan($formattedData, $selectedDate->format('Y-m-d'));
            
            if (!empty($studyPlan)) {
                $message .= "\n\n━━━━━━━━━━━━━━━━━━━━\n";
                $message .= "💡 [GỢI Ý KẾ HOẠCH HỌC TẬP]\n";
                $message .= $studyPlan;
            }
        }
        // --- KẾT THÚC BỔ SUNG ---

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
        $selectedDate = null,
        $earlyRemindItems = null
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
        
        // Xác định các ngày cần hiển thị tiêu đề (Luôn hiện tiêu đề ngày được chọn)
        $targetDates = [
            [
                'label' => $nextFormattedDate . ' (' . $nextDayNameVi . ')',
                'notes' => $notesTomorrow
            ]
        ];
        if ($dayAfterNextDate) {
            $targetDates[] = [
                'label' => $dayAfterNextFormattedDate . ' (' . $dayAfterNextDayNameVi . ')',
                'notes' => $dayAfterNextFormattedDate . ' (' . $dayAfterNextDayNameVi . ')' === $nextFormattedDate . ' (' . $nextDayNameVi . ')' ? null : $notesDayAfter
            ];
        }

        // Hiển thị bài tập theo từng ngày mục tiêu
        foreach ($targetDates as $target) {
            $dateLabel = $target['label'];
            $items = $groupedByDate->get($dateLabel, collect());
            
            // Skip nếu ngày sau trùng ngày trước (tránh lặp tiêu đề)
            if ($dayAfterNextDate && $target === $targetDates[1] && $targetDates[1]['label'] === $targetDates[0]['label']) {
                continue;
            }

            $message .= "📅 {$dateLabel}:\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
            
            if ($items->count() > 0) {
            // Group items by subject to avoid duplicates in Zalo message
            $groupedBySubject = $items->groupBy(function($data) {
                return $data['item']->subject_id;
            });

            foreach ($groupedBySubject as $subjectId => $subjectItems) {
                $firstData = $subjectItems->first();
                $item = $firstData['item'];
                $combinedContent = $subjectItems->pluck('item.content')->unique()->implode("\n");

                $message .= "• {$item->subject->name}";
                
                if ($firstData['due_date']) {
                    $dueDateStr = $firstData['due_date']->format('d/m/Y');
                    $message .= " (Hạn: {$dueDateStr})";
                }
                
                $message .= "\n";
                $message .= "  " . str_replace("\n", "\n  ", trim($combinedContent));
                $message .= "\n\n";
            }
        } else {
            $message .= "📝 Chưa có bài tập\n\n";
        }
            
            // Ghi chú chung (nếu có)
            if (!empty($target['notes'])) {
                $message .= "🗒️ Lời nhắc của GVCN / lớp trưởng:\n{$target['notes']}\n\n";
            }
        }

        
        // // Nếu không có bài tập nào
        // if ($sortedItems->count() == 0) {
        //     $message .= "📝 Chưa có bài tập cần làm trong 2 ngày tới.\n\n";
        // }

        // Append public portal link so parents/students can follow on the website
        if (!empty($portalUrl)) {
            $message .= "\n🔗 Xem thời khoá biểu & bài tập trên web:\n";
            $message .= "{$portalUrl}\n";
        }

        // Hiển thị phần nhắc nhở làm sớm
        if ($earlyRemindItems && $earlyRemindItems->count() > 0) {
            $message .= "\n━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "💡 BÀI TẬP CỦA CÁC MÔN NGÀY MAI (NÊN LÀM SỚM):\n";
            foreach ($earlyRemindItems as $item) {
                $dueDateStr = $item->due_date ? $item->due_date->format('d/m/Y') : 'Không hạn';
                $message .= "• {$item->subject->name} (Hạn: {$dueDateStr})\n";
                $message .= "  {$item->content}\n\n";
            }
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
        
        // Ưu tiên class_id từ request (dành cho cả Giáo viên và Admin)
        if ($request->has('class_id')) {
            $class = ClassModel::findOrFail($request->class_id);
            $date = $request->date ?? date('Y-m-d');
        } elseif (!$user->isAdmin()) {
            // Fallback cho giáo viên nếu không có class_id trong request
            $class = $user->getAssignedClass();
            if (!$class) {
                return redirect()->back()
                    ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
            }
            $date = $request->date ?? date('Y-m-d');
        } else {
            // Admin bắt buộc phải có class_id
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
        $timetablesRaw = Timetable::where('class_id', $class->id)
            ->where('weekday', $weekday)
            ->with('subject')
            ->orderBy('period')
            ->get();

        // Nhóm theo subject_id để tránh trùng lặp môn học
        $timetables = $timetablesRaw->groupBy('subject_id')->map(function ($group) {
            $first = $group->first();
            return (object) [
                'subject_id' => $first->subject_id,
                'subject' => $first->subject,
                'periods' => $group->pluck('period')->sort()->toArray(),
                'periods_display' => 'Tiết ' . $group->pluck('period')->sort()->implode(', '),
                'first_period' => $group->pluck('period')->min()
            ];
        })->sortBy('first_period'); // Sắp xếp theo tiết đầu tiên của môn đó

        // Kiểm tra nếu không có tiết học nào trong ngày này
        if ($timetables->isEmpty()) {
            return redirect()->route('teacher.daily-homework.list', ['class_id' => $class->id, 'date' => $date])
                ->with('error', 'Ngày này không có tiết học trong thời khóa biểu, không thể giao bài tập.');
        }

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
        
        // Ưu tiên class_id từ request
        if ($request->has('class_id')) {
            $classId = $request->class_id;
        } elseif (!$user->isAdmin()) {
            // Fallback cho giáo viên
            $class = $user->getAssignedClass();
            if (!$class) {
                return redirect()->back()
                    ->with('error', 'Bạn chưa được gán lớp nào. Vui lòng liên hệ admin.');
            }
            $classId = $class->id;
        } else {
            // Admin bắt buộc phải có class_id
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
        $timetablesRaw = Timetable::where('class_id', $class->id)
            ->where('weekday', $weekday)
            ->with('subject')
            ->orderBy('period')
            ->get();
            
        // Nhóm theo subject_id
        $timetables = $timetablesRaw->groupBy('subject_id')->map(function ($group) {
            $first = $group->first();
            return (object) [
                'subject_id' => $first->subject_id,
                'subject' => $first->subject,
                'periods' => $group->pluck('period')->sort()->toArray(),
                'periods_display' => 'Tiết ' . $group->pluck('period')->sort()->implode(', '),
                'first_period' => $group->pluck('period')->min()
            ];
        })->sortBy('first_period');

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
        
        // Cho phép xóa nếu là ngày hôm nay hoặc tương lai
        $today = now()->startOfDay();
        $homeworkDate = \Carbon\Carbon::parse($homework->date)->startOfDay();
        
        if ($homeworkDate->lessThan($today)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa bài tập trong quá khứ.',
                ], 403);
            }
            return redirect()->back()
                ->with('error', 'Không thể xóa bài tập trong quá khứ.');
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
