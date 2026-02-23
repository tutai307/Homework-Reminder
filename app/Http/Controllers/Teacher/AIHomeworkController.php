<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Timetable;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AIHomeworkController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    private function normalizeVietnamese($str)
    {
        $str = mb_strtolower($str);
        // Chuẩn hóa vị trí đặt dấu (kiểu cũ vs kiểu mới)
        $map = [
            'hoá' => 'hóa', 'hoà' => 'hòa', 'hoả' => 'hỏa', 'hoã' => 'hóa', 'hoạ' => 'họa',
            'oá' => 'óa', 'oà' => 'òa', 'oả' => 'ỏa', 'oã' => 'óa', 'oạ' => 'ọa',
            'uý' => 'úy', 'uỳ' => 'ủy', 'uỷ' => 'ủy', 'uỹ' => 'úy', 'uỵ' => 'ụy',
            'uế' => 'uế' // Thêm các cặp khác nếu cần
        ];
        return str_replace(array_keys($map), array_values($map), $str);
    }

    /**
     * Parse homework text using AI with database validation.
     */
    public function parse(Request $request)
    {
        $user = Auth::user();
        
        // 1. Validate basic input
        $request->validate([
            'text' => 'required|string|max:1000',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
        ]);

        $classId = $request->class_id;
        $date = $request->date;
        $text = $request->text;
        $normalizedText = $this->normalizeVietnamese($text);

        // 2. Kiểm tra quyền truy cập lớp
        if (!$user->hasAccessToClass($classId)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập lớp này.'], 403);
        }

        // 3. Lấy danh sách môn học từ timetable của ngày được chọn
        $weekday = Carbon::parse($date)->dayOfWeek;
        $dbWeekday = ($weekday === 0) ? 7 : $weekday;

        $timetables = Timetable::where('class_id', $classId)
            ->where('weekday', $dbWeekday)
            ->with('subject')
            ->get();

        if ($timetables->isEmpty()) {
            return response()->json([
                'success' => false, 
                'message' => 'Ngày này không có tiết học trong thời khóa biểu, không thể parse bài tập.'
            ], 422);
        }

        $validSubjects = $timetables->pluck('subject.name')->unique()->toArray();

        // 4. Kiểm tra xem text có chứa ít nhất một môn học hợp lệ không (Smart Validate)
        // Mở rộng thêm các từ khóa viết tắt phổ biến để không chặn nhầm AI
        $aliasMap = [
            'Công nghệ' => ['cn', 'c.nghệ'],
            'Âm nhạc' => ['nhạc'],
            'Tiếng Anh' => ['av', 'anh', 't.anh'],
            'Lịch sử' => ['sử'],
            'Địa lý' => ['địa'],
            'Toán học' => ['toán'],
            'Ngữ văn' => ['văn'],
            'Hóa học' => ['hóa', 'hoá'],
            'Vật lý' => ['lý'],
            'Sinh học' => ['sinh'],
            'Giáo dục công dân' => ['gdcd'],
            'Tin học' => ['tin'],
            'Thể dục' => ['td', 't.dục'],
            'Giáo dục địa phương' => ['gdđp', 'địa phương'],
            'Trải nghiệm hướng nghiệp' => ['tnp', 'trải nghiệm'],
        ];

        $foundSubject = false;
        foreach ($validSubjects as $subjectName) {
            $normalizedSubject = $this->normalizeVietnamese($subjectName);
            // Kiểm tra tên chính thức
            if (mb_strpos($normalizedText, $normalizedSubject) !== false) {
                $foundSubject = true;
                break;
            }
            // Kiểm tra alias (nếu có)
            foreach ($aliasMap as $official => $aliases) {
                if ($official === $subjectName || $this->normalizeVietnamese($official) === $normalizedSubject) {
                    foreach ($aliases as $alias) {
                        if (mb_strpos($normalizedText, $this->normalizeVietnamese($alias)) !== false) {
                            $foundSubject = true;
                            break 3; // Thoát 3 vòng lặp
                        }
                    }
                }
            }
        }

        if (!$foundSubject) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy môn học nào có trong thời khóa biểu hôm nay trong đoạn văn bản của bạn.'
            ], 422);
        }

        // 5. Gọi AI để parse
        $results = $this->aiService->parseHomework($text, $validSubjects);

        // 6. Validate lại kết quả AI (chỉ giữ lại môn học có trong TKB)
        $validatedResults = [];
        foreach ($results as $item) {
            if (isset($item['subject'])) {
                // Tìm môn học tương ứng trong danh sách hợp lệ (không phân biệt hoa thường and dấu)
                $matchedSubject = null;
                $normalizedItemSubject = $this->normalizeVietnamese($item['subject']);
                
                foreach ($timetables as $tt) {
                    if ($this->normalizeVietnamese($tt->subject->name) === $normalizedItemSubject) {
                        $matchedSubject = $tt;
                        break;
                    }
                }

                if ($matchedSubject) {
                    $validatedResults[] = [
                        'subject_id' => $matchedSubject->subject_id,
                        'subject_name' => $matchedSubject->subject_name ?? $matchedSubject->subject->name,
                        'content' => $item['content'] ?? '',
                        'due_date' => $item['due_date'] ?? null,
                    ];
                }
            }
        }

        if (empty($validatedResults)) {
            return response()->json([
                'success' => false,
                'message' => 'AI không thể xác định được bài tập cụ thể cho các môn học hợp lệ.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $validatedResults
        ]);
    }
}
