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
     * Lấy bài tập cần làm hôm sau và hôm sau nữa (không phải hôm nay).
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
        
        // Lấy bài tập cho ngày hôm sau (từ ngày được chọn)
        $nextDate = $selectedDate->copy()->addDay();
        $nextDateStr = $nextDate->format('Y-m-d');
        
        // Lấy lớp để lấy thời khóa biểu
        $class = ClassModel::findOrFail($classId);
        
        // Lấy bài tập cho ngày hôm sau
        $nextDayHomework = Homework::where('class_id', $classId)
            ->where('date', $nextDateStr)
            ->with(['items.subject'])
            ->first();
        
        // Lấy bài tập cho ngày hôm sau nữa (nếu có yêu cầu)
        $dayAfterNextHomework = null;
        $dayAfterNextDate = null;
        if ($includeDayAfterNext) {
            $dayAfterNextDate = $selectedDate->copy()->addDays(2);
            $dayAfterNextDateStr = $dayAfterNextDate->format('Y-m-d');
            
            $dayAfterNextHomework = Homework::where('class_id', $classId)
                ->where('date', $dayAfterNextDateStr)
                ->with(['items.subject'])
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

        // Format tin nhắn
        $message = $this->formatZaloMessageForUpcoming($nextDayHomework, $dayAfterNextHomework, $nextDate, $dayAfterNextDate, $timetables);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Format message for Zalo - bài tập cần làm hôm sau và hôm sau nữa (nếu có).
     */
    private function formatZaloMessageForUpcoming($nextDayHomework, $dayAfterNextHomework = null, $nextDate, $dayAfterNextDate = null, $timetables)
    {
        $today = now();
        $nextDayNameVi = $this->getDayNameVi($nextDate->dayOfWeek);
        $nextFormattedDate = $nextDate->format('d/m/Y');
        
        $dayAfterNextDayNameVi = $dayAfterNextDate ? $this->getDayNameVi($dayAfterNextDate->dayOfWeek) : '';
        $dayAfterNextFormattedDate = $dayAfterNextDate ? $dayAfterNextDate->format('d/m/Y') : '';
        
        $message = "📚 BÀI TẬP CẦN LÀM\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Xử lý bài tập ngày hôm sau
        $nextDayItems = collect();
        if ($nextDayHomework && $nextDayHomework->items->count() > 0) {
            $nextDayItems = $nextDayHomework->items->filter(function($item) {
                return !empty($item->content);
            });
        }
        
        // Xử lý bài tập ngày hôm sau nữa (nếu có)
        $dayAfterNextItems = collect();
        if ($dayAfterNextHomework && $dayAfterNextDate && $dayAfterNextHomework->items->count() > 0) {
            $dayAfterNextItems = $dayAfterNextHomework->items->filter(function($item) {
                return !empty($item->content);
            });
        }
        
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
        if ($dayAfterNextDate) {
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
        }
        
        // Nếu không có bài tập nào
        if ($sortedItems->count() == 0) {
            $message .= "📝 Chưa có bài tập cần làm trong 2 ngày tới.\n\n";
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
        $days = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
        return $days[$dayOfWeek] ?? '';
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
                    $homework->items()->create([
                        'subject_id' => $item['subject_id'],
                        'content' => $item['content'],
                        'due_date' => $item['due_date'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('teacher.daily-homework.index')
            ->with('success', 'Bài tập đã được tạo thành công.');
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
        
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'homework' => 'nullable|array',
            'homework.*.subject_id' => 'required_with:homework|exists:subjects,id',
            'homework.*.content' => 'required_with:homework|string',
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
                    $homework->items()->create([
                        'subject_id' => $item['subject_id'],
                        'content' => $item['content'],
                        'due_date' => $item['due_date'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('teacher.daily-homework.index')
            ->with('success', 'Bài tập đã được cập nhật thành công.');
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
        
        // Xóa bài tập
        $homework->delete();
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bài tập đã được xóa thành công.',
            ]);
        }
        
        return redirect()->route('teacher.daily-homework.index')
            ->with('success', 'Bài tập đã được xóa thành công.');
    }
}

