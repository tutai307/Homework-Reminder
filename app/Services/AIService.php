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

        $prompt = "Bạn là một chuyên gia tư vấn giáo dục thông minh. Nhiệm vụ của bạn là lập một KẾ HOẠCH HỌC TẬP TỐI ƯU cho học sinh dựa trên danh sách bài tập sau:

{$itemsList}

Ngày hôm nay là: {$targetDate}.

Yêu cầu phân tích và lập kế hoạch:
1. Thứ tự ưu tiên: 
   - Ưu tiên các bài có deadline gần nhất (ví dụ: mai nộp).
   - Xen kẽ các môn khó (Toán, KHTN) với các môn học thuộc (Văn, Sử, Địa) để tránh căng thẳng.
   - Ưu tiên bài tập có khối lượng nhiều làm trước khi còn tỉnh táo.

2. Ước tính thời gian:
   - Ước lượng thời gian thực tế phù hợp với học sinh THCS/THPT (ví dụ: làm 5-10 bài Toán mất 40-50p, soạn bài Văn mất 20p...).
   - Đưa ra tổng thời gian dự kiến.

3. Quy định định dạng (CỰC KỲ QUAN TRỌNG):
   - Trả về văn bản THUẦN (Plain Text) để gửi qua Zalo.
   - KHÔNG sử dụng Markdown (không dùng **, ##, [ ], _, *).
   - KHÔNG dùng JSON hay ký tự đặc biệt phức tạp.
   - Mỗi ý một dòng, có dấu gạch đầu dòng (-) hoặc số thứ tự (1, 2, 3).
   - Viết ngắn gọn, súc tích, ngôn ngữ tự nhiên, khích lệ học sinh.

Nội dung kế hoạch phải bắt đầu ngay bằng các gợi ý hành động cụ thể.";

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
