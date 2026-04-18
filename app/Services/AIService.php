<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class AIService
{
    /**
     * Parse homework from free text based on valid subjects.
     *
     * @param string $text
     * @param array $subjects List of valid subject names
     * @return array
     */
    public function parseHomework(string $text, array $subjects): array
    {
        $subjectsList = implode(', ', $subjects);
        
        $now = \Carbon\Carbon::now();
        $nowDate = $now->format('Y-m-d');
        $dayOfWeek = $now->dayOfWeek; // 0 (Sun) to 6 (Sat)
        
        $prompt = "Bạn là một trợ lý giáo vụ thông minh. Nhiệm vụ của bạn là trích xuất và chuẩn hóa bài tập về nhà từ văn bản tiếng Việt lộn xộn.

Danh sách các môn học CHÍNH THỨC hợp lệ: [{$subjectsList}].

Quy tắc trích xuất ĐẶC BIỆT:

1. Xử lý môn học Ghép (Ví dụ: 'Lịch sử và Địa lí'):
   - Nếu văn bản có nội dung riêng cho 'Sử' và 'Địa' (như: 'Sử làm bài 7, Địa mang Atlat'), hãy hiểu cả hai đều thuộc môn 'Lịch sử và Địa lí'.
   - Bạn có thể tạo nhiều đối tượng JSON cho cùng một môn chính thức nếu chúng có nội dung hoặc hạn nộp khác nhau.

2. Nhận diện Viết tắt & Biến thể:
   - Cực kỳ linh hoạt với: GDCD (Giáo dục công dân), GDTC (GDTC/Thể dục), KHTN (Khoa học tự nhiên), Văn (Ngữ văn), Anh/AV (Tiếng Anh), Sử/Địa (Lịch sử và Địa lý), CN (Công nghệ), Tin (Tin học), NT (Nghệ thuật)...
   - 'subject' trong kết quả PHẢI LUÔN là tên CHÍNH THỨC từ danh sách trên.

3. Chuẩn hóa Nội dung (Content):
   - Trích xuất yêu cầu cụ thể (ví dụ: 'Làm bài 7 trang 14 SGK').
   - Nếu nội dung lồng ghép nhiều phần, hãy viết mạch lạc.

4. Chuẩn hóa Ngày nộp (Due Date):
   - Hôm nay: {$nowDate} (Thứ {$dayOfWeek}).
   - 'mai' -> " . $now->copy()->addDay()->format('Y-m-d') . ".
   - 'tuần sau' -> " . $now->copy()->addDays(7)->format('Y-m-d') . ".
   - 'thứ X' -> Tính toán ngày thứ X gần nhất sắp tới.
   - Định dạng: YYYY-MM-DD.

5. Định dạng đầu ra:
   - Trả về mảng JSON thuần túy.
   - Ví dụ đầu vào: 'Sử làm bài 7 trang 14sgk tuần sau kiểm tra, Địa mang alas'
   - Ví dụ đầu ra: [
       {\"subject\": \"Lịch sử và Địa lí\", \"content\": \"(Sử) làm bài 7 trang 14 SGK - tuần sau kiểm tra\", \"due_date\": \"{$now->copy()->addDays(7)->format('Y-m-d')}\"},
       {\"subject\": \"Lịch sử và Địa lí\", \"content\": \"(Địa) mang Atlat, học kĩ để kiểm tra miệng\", \"due_date\": null}
     ]

Văn bản đầu vào: \"{$text}\"";

        try {
            $apiKey = config('openai.api_key');
            $baseUrl = 'https://api.openai.com/v1';
            
            if ($apiKey && str_starts_with($apiKey, 'sk-or-')) {
                $baseUrl = 'https://openrouter.ai/api/v1';
            }

            // Log chuẩn đoán (không log toàn bộ key để bảo mật)
            \Log::info('Initiating AI Parse request', [
                'base_url' => $baseUrl,
                'key_length' => strlen($apiKey ?? ''),
                'key_prefix' => substr($apiKey ?? '', 0, 7) . '...',
            ]);

            $client = new \GuzzleHttp\Client([
                'timeout' => config('openai.request_timeout', 30),
            ]);

            $response = $client->post($baseUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'openai/gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Bạn là một chuyên gia trích xuất dữ liệu bài tập trường học từ văn bản tiếng Việt mẫu mực.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (isset($result['error'])) {
                \Log::error('AI Provider Error: ' . json_encode($result['error']));
                return [];
            }

            $content = $result['choices'][0]['message']['content'] ?? '';
            
            // Tìm và trích xuất JSON từ chuỗi nếu AI trả về kèm markdown
            if (preg_match('/\[.*\]/s', $content, $matches)) {
                $content = $matches[0];
            }

            $data = json_decode($content, true);
            
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            $errorMsg = get_class($e) . ': ' . $e->getMessage();
            \Log::error('OpenAI/OpenRouter Parse Error: ' . $errorMsg);
            
            if ($e instanceof \GuzzleHttp\Exception\ClientException && $e->hasResponse()) {
                \Log::error('Detailed Error Response: ' . $e->getResponse()->getBody()->getContents());
            }
            return [];
        }
    }

    /**
     * Parse timetable from an image.
     *
     * @param string $imageBase64 Base64 encoded image data
     * @param array $subjects Array of valid subject names
     * @param string $className The target class name to focus on
     * @return array
     */
    public function parseTimetableFromImage(string $imageBase64, array $subjects, string $className): array
    {
        $subjectsList = implode(', ', $subjects);
        
        $prompt = "You are a highly accurate Visual Data Extraction AI. Your task is to extract the school timetable for class \"{$className}\" from the provided image into a structured JSON format.

### CRITICAL INSTRUCTIONS TO PREVENT COLUMN SHIFTING:
1. **Grid Alignment**: The table has headers for 'THỨ' (days of the week) from 'THỨ 2' to 'THỨ 7'. There are 6 data columns in total.
2. **Strict Row-by-Row Processing**: 
   - Analyze each row (Period/Tiết) independently.
   - **DO NOT SHIFT DATA**: If a cell is empty (no text), you MUST return `null`. Never move a subject from a later column to an earlier column to fill a gap.
   - **Specific Case (Row 5)**: In the 'Buổi sáng' section, Row 5 often has empty cells for 'Thứ 2' and 'Thứ 3'. You MUST return `null` for these. Do not put 'Tiếng Anh' (from Thứ 4) into 'Thứ 2'.

### SUBJECT MAPPING (VIETNAMESE):
Map the detected text/abbreviations to these EXACT official names:
- 'SHL', 'Sinh hoạt', 'Chào cờ', 'Lớp' -> 'Sinh hoạt'
- 'Toán' -> 'Toán'
- 'Văn', 'Ngữ văn' -> 'Ngữ văn'
- 'T.Anh', 'Anh' -> 'Tiếng Anh'
- 'KHTN' -> 'Khoa học tự nhiên'
- 'LSĐL-Đ', 'LSĐL-S', 'Sử', 'Địa', 'LS-ĐL' -> 'Lịch sử và Địa lí'
- 'HĐTNHN', 'HĐTN', 'HĐTN-4', 'Trải nghiệm' -> 'HĐTN HN'
- 'GDTC', 'Thể dục' -> 'GDTC'
- 'GDĐP', 'Địa phương' -> 'Giáo dục ĐP'
- 'CNghệ', 'CN', 'Công nghệ' -> 'Công nghệ'
- 'NT-MT', 'NT-AN', 'Mỹ thuật', 'Âm nhạc', 'NT' -> 'Nghệ thuật'
- 'GDCD' -> 'Giáo dục công dân'
- 'Tin' -> 'Tin học'

### VALID SUBJECTS LIST:
[{$subjectsList}]

### ROW-SPECIFIC VERIFICATION:
- **Tuesday Afternoon (Row 9)**: Look closely at the bottom of the 'Thứ 3' column in the 'Buổi chiều' section. You should see 'SHL'. This MUST be mapped to 'Sinh hoạt'. Do NOT repeat 'Khoa học tự nhiên' here.
- **Strict Coordinate Check**: Row 9 is the 4th row under 'Buổi chiều'. Column 3 is 'THỨ 3'.

### OUTPUT FORMAT:
Return ONLY a valid JSON object. No explanations.
{
  \"1\": { \"2\": \"Subject Name\", \"3\": \"...\", \"4\": \"...\", \"5\": \"...\", \"6\": \"...\", \"7\": \"...\" },
  ...
  \"10\": { ... }
}
(Note: Periods 1-5 are Morning, 6-10 are Afternoon).";

        try {
            $apiKey = config('openai.api_key');
            $baseUrl = 'https://api.openai.com/v1';
            
            if ($apiKey && str_starts_with($apiKey, 'sk-or-')) {
                $baseUrl = 'https://openrouter.ai/api/v1';
            }

            $client = new \GuzzleHttp\Client([
                'timeout' => 60, // Tăng timeout cho xử lý hình ảnh
            ]);

            $messages = [
                [
                    'role' => 'system',
                    'content' => "You are a professional Visual Data Extraction AI.
### EXAMPLE OF CORRECT EXTRACTION FOR THIS STYLE:
If Row 5 Morning has: [Empty, Empty, T.Anh, CNghệ, CNghệ]
JSON should be: \"5\": { \"2\": null, \"3\": null, \"4\": \"Tiếng Anh\", \"5\": \"Công nghệ\", \"6\": \"Công nghệ\", \"7\": null }

If Row 4 Afternoon (Tiết 9) has: [Empty, SHL, Empty, Empty, Empty]
JSON should be: \"9\": { \"2\": null, \"3\": \"Sinh hoạt\", \"4\": null, \"5\": null, \"6\": null, \"7\": null }

### RULES:
1. DO NOT SHIFT COLUMNS.
2. EMPTY = NULL.
3. MAP ABBREVIATIONS: SHL -> Sinh hoạt, KHTN -> Khoa học tự nhiên, NT-MT/NT-AN -> Nghệ thuật."
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageBase64
                            ]
                        ]
                    ]
                ]
            ];

            $response = $client->post($baseUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'openai/gpt-4o',
                    'messages' => $messages,
                    'temperature' => 0,
                    'max_tokens' => 2000,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $content = $result['choices'][0]['message']['content'] ?? '';
            \Log::info('AI Timetable Response: ' . $content);
            
            // Trích xuất JSON từ markdown nếu cần
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $content = $matches[0];
            }

            $data = json_decode($content, true);
            
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            \Log::error('AI Timetable Image Parse Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate a study plan based on homework items.
     *
     * @param array $items Array of items with 'subject', 'content', and 'due_date'
     * @param string $targetDate The date for which the study plan is being generated
     * @return string
     */
    public function generateStudyPlan(array $items, string $targetDate): string
    {
        if (count($items) < 2) {
            return "";
        }

        $itemsList = "";
        foreach ($items as $index => $item) {
            $dueDate = $item['due_date'] ?? 'Không rõ';
            $itemsList .= ($index + 1) . ". Môn: {$item['subject']} - Bài tập: {$item['content']} (Hạn nộp: {$dueDate})\n";
        }

        $prompt = "Bạn là một chuyên gia tư vấn giáo dục thông minh. Nhiệm vụ của bạn là phân tích lượng bài tập và lập một KẾ HOẠCH HỌC TẬP TỐI ƯU cho học sinh dựa trên danh sách bài tập sau:

{$itemsList}

Ngày thực hiện kế hoạch (Hôm nay): {$targetDate}.

Yêu cầu phân tích và lập kế hoạch CHI TIẾT CHỈ CHO HÔM NAY VÀ NGÀY MAI:
1. Phân tích tổng quan: Đánh giá nhanh lượng bài tập cần xử lý trong 2 ngày (hôm nay và ngày mai).
2. Chiến thuật làm bài (Trình tự thông minh): 
   - Đề xuất môn nào làm trước, môn nào làm sau. Phân bổ hợp lý cho tối nay và ngày mai.
   - Chiến thuật: Bắt đầu bằng một môn dễ/yêu thích để tạo đà (5-10p), sau đó tập trung vào môn khó nhất/hạn nộp gần nhất khi não còn tỉnh táo, cuối cùng là các môn học thuộc nhẹ nhàng.
3. Ước tính thời gian THỰC TẾ (CỰC KỲ QUAN TRỌNG):
   - Ước lượng thời gian tối giản và hiệu quả, tránh đưa ra thời gian quá dài không thực tế (VD: Soạn văn chỉ 15-30p, bài tập Toán 30-45p, không để 90-120p cho các bài tập thông thường).
   - Đưa ra thời gian dự kiến cho từng môn và tổng thời gian tối ưu toàn bộ.
4. Lời khuyên ngắn (Pomodoro, nghỉ ngơi): Nhắc nhanh việc nghỉ giải lao.

QUY ĐỊNH ĐỊNH DẠNG (BẮT BUỘC):
- Trả về văn bản THUẦN (Plain Text) để copy gửi Zalo.
- TUYỆT ĐỐI KHÔNG dùng Markdown (không dùng **, ##, [ ], _, *, `).
- Sử dụng dấu cộng (+) hoặc số thứ tự (1. 2. 3.) cho các ý.
- Viết cực kỳ ngắn gọn, súc tích, ngôn ngữ tự nhiên, khích lệ.
- Bắt đầu nội dung ngay không cần chào hỏi.";

        try {
            $apiKey = config('openai.api_key');
            $baseUrl = 'https://api.openai.com/v1';
            
            if ($apiKey && str_starts_with($apiKey, 'sk-or-')) {
                $baseUrl = 'https://openrouter.ai/api/v1';
            }

            $client = new \GuzzleHttp\Client([
                'timeout' => config('openai.request_timeout', 30),
            ]);

            $response = $client->post($baseUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'openai/gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Bạn là chuyên gia lập kế hoạch học tập. Hãy viết bằng tiếng Việt, thân thiện, súc tích, không dùng định dạng markdown.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            // Dọn dẹp sơ bộ nếu AI vô tình trả về markdown
            $content = str_replace(['**', '##', '###', '_', '*', '`'], '', $content);
            
            return trim($content);
        } catch (\Exception $e) {
            \Log::error('Generate Study Plan Error: ' . $e->getMessage());
            return "";
        }
    }
}
