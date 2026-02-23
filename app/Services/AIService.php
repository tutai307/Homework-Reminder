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
}
