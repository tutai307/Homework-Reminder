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
        
        $prompt = "Bạn là chuyên gia trích xuất dữ liệu từ hình ảnh thời khóa biểu.
NHIỆM VỤ: Trích xuất thời khóa biểu của lớp \"{$className}\".

QUY TẮC ĐỌC ẢNH (BẮT BUỘC):
1. Đọc theo từng HÀNG NGANG (tương ứng với các TIẾT học).
2. Hình ảnh thường chia thành 2 phần: \"Buổi sáng\" và \"Buổi chiều\".
   - 5 hàng đầu tiên dưới tiêu đề Buổi sáng là Tiết 1, 2, 3, 4, 5.
   - 5 hàng tiếp theo dưới tiêu đề Buổi chiều là Tiết 6, 7, 8, 9, 10.
3. Với mỗi HÀNG (Tiết), hãy liệt kê môn học của các THỨ (từ Thứ 2 đến Thứ 7).

CẤU TRÚC JSON TRẢ VỀ (BẮT BUỘC):
{
  \"1\": { \"2\": \"Môn A\", \"3\": \"Môn B\", \"4\": \"Môn C\", \"5\": \"Môn D\", \"6\": \"Môn E\", \"7\": \"Môn F\" }, // Tiết 1
  \"2\": { \"2\": \"...\", \"3\": \"...\", ... }, // Tiết 2
  ...
  \"10\": { \"2\": \"...\", ... } // Tiết 10
}
- Key cấp 1: Số TIẾT học (từ 1 đến 10).
- Key cấp 2: Số THỨ (từ 2 đến 7).

QUY TRÌNH ĐỐI SOÁT MÔN HỌC:
- Danh sách môn hợp lệ: [{$subjectsList}].
- Tên môn học phải khớp hoặc gần đúng nhất với danh sách trên (VD: 'LS-ĐL' -> 'Lịch sử và Địa lí').

LƯU Ý:
- Nếu ô trống, trả về null.
- Chỉ trả về JSON thuần túy, không giải thích.";

        try {
            $apiKey = config('openai.api_key');
            $baseUrl = 'https://api.openai.com/v1';
            
            if ($apiKey && str_starts_with($apiKey, 'sk-or-')) {
                $baseUrl = 'https://openrouter.ai/api/v1';
            }

            $client = new \GuzzleHttp\Client([
                'timeout' => 60, // Tăng timeout cho xử lý hình ảnh
            ]);

            // Chuẩn bị payload cho GPT-4o-Vision (qua OpenRouter hoặc OpenAI trực tiếp)
            $messages = [
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
                                'url' => $imageBase64 // Hy vọng base64 đã bao gồm tiền tố data:image/...
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
                    'model' => 'openai/gpt-4o', // Model này hỗ trợ Vision và rẻ/nhanh
                    'messages' => $messages,
                    'temperature' => 0.1,
                    'max_tokens' => 2000,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $content = $result['choices'][0]['message']['content'] ?? '';
            
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
