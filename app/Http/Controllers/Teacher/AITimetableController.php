<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AITimetableController extends Controller
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
        
        $str = preg_replace('/í|ý/u', 'i', $str);
        $str = preg_replace('/ì|ỳ/u', 'i', $str);
        $str = preg_replace('/ỉ|ỷ/u', 'i', $str);
        $str = preg_replace('/ĩ|ỹ/u', 'i', $str);
        $str = preg_replace('/ị|ỵ/u', 'i', $str);
        $str = str_replace('y', 'i', $str);

        $map = [
            'hoá' => 'hóa', 'hoà' => 'hòa', 'hoả' => 'hỏa', 'hoã' => 'hóa', 'hoạ' => 'họa',
            'oá' => 'óa', 'oà' => 'òa', 'oả' => 'ỏa', 'oã' => 'óa', 'oạ' => 'ọa',
            'uý' => 'úy', 'uỳ' => 'ủy', 'uỷ' => 'ủy', 'uỹ' => 'úy', 'uỵ' => 'ụy'
        ];
        $str = str_replace(array_keys($map), array_values($map), $str);

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
     * Parse timetable from image.
     */
    public function parseImage(Request $request, ClassModel $class)
    {
        $user = Auth::user();

        if (!$user->hasAccessToClass($class->id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập lớp này.'], 403);
        }

        $request->validate([
            'image' => 'required|string', // Base64 image
        ]);

        $subjects = Subject::all();
        $subjectNames = $subjects->pluck('name')->toArray();

        $aiResults = $this->aiService->parseTimetableFromImage($request->image, $subjectNames, $class->name);

        if (empty($aiResults)) {
            return response()->json([
                'success' => false,
                'message' => 'AI không thể phân tích được hình ảnh. Vui lòng thử lại với ảnh rõ nét hơn.'
            ], 422);
        }

        // AI trả về [Tiết][Thứ] -> Cần xoay lại thành [Thứ][Tiết] cho UI
        $formattedData = [];
        // Khởi tạo mảng trống từ Thứ 2 (1) đến Thứ 7 (6)
        for ($w = 1; $w <= 6; $w++) {
            $formattedData[$w] = [];
            for ($p = 1; $p <= 10; $p++) {
                $formattedData[$w][$p] = null;
            }
        }

        foreach ($aiResults as $periodKey => $weekdays) {
            $period = (int)$periodKey;
            if ($period < 1 || $period > 10) continue;

            foreach ($weekdays as $weekdayKey => $subjectName) {
                // AI trả về Thứ 2 là key "2", ..., Thứ 7 là key "7"
                // UI cần Thứ 2 là index 1, ..., Thứ 7 là index 6
                $weekday = (int)$weekdayKey - 1;
                
                if ($weekday < 1 || $weekday > 6) continue;
                if (empty($subjectName)) continue;

                $subjectId = $this->findBestMatch($subjectName, $subjects);
                if ($subjectId) {
                    $formattedData[$weekday][$period] = $subjectId;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $formattedData
        ]);
    }

    /**
     * Tìm ID môn học khớp nhất từ tên
     */
    private function findBestMatch($subjectName, $subjects)
    {
        $normalizedInput = $this->normalizeVietnamese($subjectName);
        
        // 1. Ưu tiên so khớp TRỰC TIẾP toàn bộ tên (chính xác nhất)
        foreach ($subjects as $subject) {
            $normalizedOfficial = $this->normalizeVietnamese($subject->name);
            if ($normalizedOfficial === $normalizedInput) {
                return $subject->id;
            }
        }

        // 2. So khớp qua Alias Map (toàn bộ input)
        $aliasMap = [
            'toan' => ['toán', 'toán học'],
            'ngu van' => ['văn', 'ngữ văn', 'nv'],
            'tieng anh' => ['anh', 'tiếng anh', 't.anh', 'anh văn', 'en'],
            'khoa hoc tu nhien' => ['khtn', 'tự nhiên', 'lý', 'hóa', 'sinh', 'vật lý', 'hóa học', 'sinh học'],
            'lich su va dia li' => ['ls-đl', 'sử', 'địa', 'lsđl', 'lsđl-đ', 'lsđl-s', 'sử địa'],
            'gdtc' => ['thể dục', 'gdTC', 'td', 'thể chất'],
            'tin hoc' => ['tin', 'it', 'tin học'],
            'cong nghe' => ['cn', 'công nghệ', 'cnghệ'],
            'giao duc cong dan' => ['gdcd', 'công dân'],
            'nghe thuat' => ['mỹ thuật', 'âm nhạc', 'nt', 'nghệ thuật', 'nt-mt', 'nt-an', 'mĩ thuật'],
            'hdtn hn' => ['hđtn', 'trải nghiệm', 'hướng nghiệp', 'hdtn', 'hđtn hn', 'hdtn-4', 'hdtn 4', 'hđtn hn'],
            'giao duc dp' => ['gdđp', 'địa phương', 'gdđp', 'giáo dục đp', 'gddp'],
            'sinh hoat' => ['shl', 'sinh hoạt', 'sh', 'sinh hoạt lớp', 'chào cờ', 'sh dưới cờ', 'sinh hoạt lớp'],
        ];

        foreach ($subjects as $subject) {
            $normalizedOfficial = $this->normalizeVietnamese($subject->name);
            foreach ($aliasMap as $officialAlias => $aliases) {
                if ($this->normalizeVietnamese($officialAlias) === $normalizedOfficial) {
                    foreach ($aliases as $alias) {
                        if ($this->normalizeVietnamese($alias) === $normalizedInput) {
                            return $subject->id;
                        }
                    }
                }
            }
        }

        // 3. So khớp phần đầu (chỉ áp dụng nếu input dài và có phần phân tách)
        $cleanedInput = str_replace('-', ' ', $subjectName);
        $parts = preg_split('/[\s]/u', $cleanedInput, 2);
        if (count($parts) > 1) {
            $mainPart = $this->normalizeVietnamese($parts[0]);
            foreach ($subjects as $subject) {
                $normalizedOfficial = $this->normalizeVietnamese($subject->name);
                if ($normalizedOfficial === $mainPart) {
                    return $subject->id;
                }
                foreach ($aliasMap as $officialAlias => $aliases) {
                    if ($this->normalizeVietnamese($officialAlias) === $normalizedOfficial) {
                        foreach ($aliases as $alias) {
                            if ($this->normalizeVietnamese($alias) === $mainPart) {
                                // Cảnh báo: Tránh khớp nhầm "Sinh" trong "Sinh hoạt" với môn "Sinh học"
                                if ($mainPart === 'sinh' && str_contains($normalizedInput, 'hoat')) {
                                    continue;
                                }
                                return $subject->id;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
