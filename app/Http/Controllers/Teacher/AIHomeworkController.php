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
        if (!$str) return '';
        $str = mb_strtolower($str);
        
        // 1. Xử lý i/y (Chuyển hết về i)
        $str = preg_replace('/í|ý/u', 'i', $str);
        $str = preg_replace('/ì|ỳ/u', 'i', $str);
        $str = preg_replace('/ỉ|ỷ/u', 'i', $str);
        $str = preg_replace('/ĩ|ỹ/u', 'i', $str);
        $str = preg_replace('/ị|ỵ/u', 'i', $str);
        $str = str_replace('y', 'i', $str);

        // 2. Chuẩn hóa vị trí đặt dấu
        $map = [
            'hoá' => 'hóa', 'hoà' => 'hòa', 'hoả' => 'hỏa', 'hoã' => 'hóa', 'hoạ' => 'họa',
            'oá' => 'óa', 'oà' => 'òa', 'oả' => 'ỏa', 'oã' => 'óa', 'oạ' => 'ọa',
            'uý' => 'úy', 'uỳ' => 'ủy', 'uỷ' => 'ủy', 'uỹ' => 'úy', 'uỵ' => 'ụy'
        ];
        $str = str_replace(array_keys($map), array_values($map), $str);

        // 3. Loại bỏ dấu tiếng Việt hoàn toàn để so sánh "thô"
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩn|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            's' => 's',
            'x' => 'x'
        ];
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }

        return trim($str);
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

        // 2. Kiểm tra quyền truy cập lớp và chặn giao bài tập quá khứ
        if (!$user->hasAccessToClass($classId)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập lớp này.'], 403);
        }

        $today = Carbon::now()->startOfDay();
        $selectedDate = Carbon::parse($date)->startOfDay();
        if ($selectedDate->lessThan($today)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể sử dụng AI để giao bài tập cho ngày trong quá khứ.'
            ], 422);
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

        $validOfficialSubjects = $timetables->pluck('subject.name')->unique()->toArray();

        // 4. Xây dựng DYNAMIC SUBJECT DICTIONARY (Cơ chế alias mở rộng)
        $globalAliasMap = [
            'Toan' => ['toán', 't', 'toán học'],
            'Ngu van' => ['văn', 'nv', 'ngữ văn', 'soạn văn', 'tiếng việt'],
            'Tieng Anh' => ['anh', 'av', 'english', 't.anh', 'tiếng anh'],
            'Khoa hoc tu nhien' => ['khtn', 'tự nhiên', 'lý', 'hóa', 'sinh', 'vật lý', 'hóa học', 'sinh học'],
            'Lich su va Dia li' => ['lsđl', 'sử địa', 'sử', 'địa', 'lịch sử', 'địa lý', 'ls', 'đl'],
            'Lich su' => ['sử', 'ls', 'lịch sử'],
            'Dia li' => ['địa', 'đl', 'địa lý'],
            'Giao duc cong dan' => ['gdcd', 'công dân', 'đạo đức'],
            'Tin hoc' => ['tin', 'th', 'tin học'],
            'Cong nghe' => ['cn', 'công nghệ'],
            'GDTC' => ['gdtc', 'thể dục', 'td', 'tập thể dục', 'gdtc'],
            'Nghe thuat' => ['vẽ', 'nhạc', 'mỹ thuật', 'âm nhạc', 'mĩ thuật', 'nt'],
            'Am nhac' => ['nhạc', 'an', 'âm nhạc'],
            'Mi thuat' => ['vẽ', 'mt', 'mỹ thuật', 'mĩ thuật'],
            'HDTN HN' => ['hđtn', 'trải nghiệm', 'hướng nghiệp', 'hdtn'],
            'Giao duc DP' => ['gdđp', 'địa phương', 'gdđp'],
            'Sinh hoat' => ['shl', 'sinh hoạt', 'sh'],
        ];

        // Bước này cực kỳ quan trọng: Local Pre-parsing
        $detectedOfficialNames = [];

        foreach ($validOfficialSubjects as $officialName) {
            $normalizedOfficial = $this->normalizeVietnamese($officialName);
            
            // Tìm các alias từ map dựa trên tên đã chuẩn hóa
            $aliases = [];
            foreach ($globalAliasMap as $key => $val) {
                if ($this->normalizeVietnamese($key) === $normalizedOfficial) {
                    $aliases = $val;
                    break;
                }
            }

            // Danh sách các từ cần tìm kiếm (đã chuẩn hóa)
            $searchTerms = array_unique(array_merge(
                [$normalizedOfficial], 
                array_map([$this, 'normalizeVietnamese'], $aliases)
            ));
            
            foreach ($searchTerms as $term) {
                if ($term && mb_strpos($normalizedText, $term) !== false) {
                    $detectedOfficialNames[] = $officialName;
                    break; 
                }
            }
        }

        // TỐI ƯU HÓA: Chỉ gọi AI nếu tìm thấy ít nhất 1 môn học khớp với DB
        if (empty($detectedOfficialNames)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy môn học nào trong văn bản khớp với thời khóa biểu hôm nay. Vui lòng kiểm tra lại tên môn học hoặc viết đúng tên môn (ví dụ: Toán, Văn, Anh...).'
            ], 422);
        }

        // 5. Gọi AI để parse (Chỉ gửi danh sách các môn đã detect được để AI tập trung hơn)
        $results = $this->aiService->parseHomework($text, $detectedOfficialNames);

        // 6. Validate lại kết quả AI (chỉ giữ lại môn học có trong TKB)
        $validatedResults = [];
        foreach ($results as $item) {
            if (isset($item['subject'])) {
                // Tìm môn học tương ứng trong danh sách hợp lệ
                $matchedSubject = null;
                $normalizedItemSubject = $this->normalizeVietnamese($item['subject']);
                
                foreach ($timetables as $tt) {
                    $officialName = $tt->subject->name;
                    $normalizedOfficial = $this->normalizeVietnamese($officialName);

                    // 1. So khớp trực tiếp
                    if ($normalizedOfficial === $normalizedItemSubject) {
                        $matchedSubject = $tt;
                        break;
                    }

                    // 2. So khớp qua aliasMap
                    if (isset($aliasMap[$officialName])) {
                        foreach ($aliasMap[$officialName] as $alias) {
                            if ($this->normalizeVietnamese($alias) === $normalizedItemSubject) {
                                $matchedSubject = $tt;
                                break 2;
                            }
                        }
                    }
                }

                if ($matchedSubject) {
                    $dueDate = $item['due_date'] ?? null;
                    
                    // Logic: Hạn nộp phải ít nhất là ngày hôm nay
                    if ($dueDate) {
                        try {
                            $parsedDue = Carbon::parse($dueDate)->startOfDay();
                            if ($parsedDue->lessThan($today)) {
                                $dueDate = null;
                            }
                        } catch (\Exception $e) {
                            $dueDate = null;
                        }
                    }

                    $validatedResults[] = [
                        'subject_id' => $matchedSubject->subject_id,
                        'subject_name' => $matchedSubject->subject->name,
                        'content' => $item['content'] ?? '',
                        'due_date' => $dueDate,
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

        // Gộp kết quả theo môn học (gom content)
        $groupedResults = collect($validatedResults)->groupBy('subject_id')->map(function ($items) {
            $first = $items->first();
            
            // Nếu các mục cùng môn có nội dung khác nhau, gộp lại bằng xuống dòng
            $combinedContent = $items->pluck('content')
                ->filter()
                ->unique()
                ->implode("\n");

            // Ưu tiên deadline sớm nhất cho môn đó (hoặc giữ nguyên nếu cùng deadline)
            $earliestDueDate = $items->pluck('due_date')->filter()->min();

            return [
                'subject_id' => $first['subject_id'],
                'subject_name' => $first['subject_name'],
                'content' => $combinedContent,
                'due_date' => $earliestDueDate,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => $groupedResults
        ]);
    }
}
