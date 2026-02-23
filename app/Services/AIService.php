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
        
        $now = \Carbon\Carbon::now()->format('Y-m-d');
        $prompt = "Bạn là một trợ lý giáo vụ chuyên nghiệp. Nhiệm vụ của bạn là trích xuất thông tin bài tập từ văn bản tiếng Việt.
Danh sách các môn học hợp lệ: [{$subjectsList}].

Quy tắc trích xuất:
1. Chỉ trích xuất bài tập cho các môn học có tên (hoặc từ viết tắt tương đương) trong danh sách hợp lệ ở trên. 
   - AI phải thông minh để hiểu các từ viết tắt phổ biến: CN (Công nghệ), Nhạc (Âm nhạc), AV/Tiếng Anh (Tiếng Anh), Sử (Lịch sử), Địa (Địa lý), GDCD (Giáo dục công dân),...
   - Kết quả 'subject' PHẢI là tên chính thức từ danh sách hợp lệ.
2. Với mỗi môn học tìm thấy, hãy tách rõ:
   - 'subject': Tên môn học (PHẢI TRÙNG KHỚP HOÀN TOÀN với tên trong danh sách hợp lệ).
   - 'content': Nội dung yêu cầu bài tập (ví dụ: 'làm bài 1,2 trang 45'). KHÔNG bao gồm thông tin về hạn nộp trong này.
   - 'due_date': Hạn nộp bài tập. Định dạng bắt buộc: YYYY-MM-DD. 
     - Ngày hiện tại (Today) là: {$now}.
     - Quy tắc quan trọng: 'due_date' PHẢI từ ngày hiện tại ({$now}) trở về sau. Tuyệt đối không trích xuất ngày trong quá khứ.
     - Nếu người dùng ghi 'hạn 2/3' và năm hiện tại là 2026, hãy chuyển thành '2026-03-02'. 
     - Nếu ghi 'hạn tuần sau', hãy tự tính toán dựa trên ngày hiện tại là {$now}. 
     - Nếu không có hạn nộp hoặc hạn nộp là ngày quá khứ, hãy để null.
3. Kết quả trả về là mảng JSON các đối tượng.
4. Nếu không có bài tập nào cho các môn hợp lệ, trả về mảng rỗng [].
5. Chỉ trả về JSON thuần túy, không giải thích.

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
