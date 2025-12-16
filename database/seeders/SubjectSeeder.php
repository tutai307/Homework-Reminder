<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // Môn học cơ bản
            ['name' => 'Toán', 'code' => 'TOAN'],
            ['name' => 'Ngữ văn', 'code' => 'NGUVAN'],
            ['name' => 'Tiếng Anh', 'code' => 'TA'],
            ['name' => 'Vật lý', 'code' => 'VATLY'],
            ['name' => 'Hóa học', 'code' => 'HOAHOC'],
            ['name' => 'Sinh học', 'code' => 'SINH'],
            ['name' => 'Lịch sử', 'code' => 'LS'],
            ['name' => 'Địa lý', 'code' => 'DL'],
            ['name' => 'Giáo dục công dân', 'code' => 'GDCD'],
            ['name' => 'Công nghệ', 'code' => 'CN'],
            ['name' => 'Tin học', 'code' => 'TIN'],
            ['name' => 'Thể dục', 'code' => 'TD'],
            ['name' => 'Mỹ thuật', 'code' => 'MT'],
            ['name' => 'Âm nhạc', 'code' => 'AM'],
            
            // Môn học chuyên sâu (THPT)
            ['name' => 'Toán nâng cao', 'code' => 'TOANNC'],
            ['name' => 'Vật lý nâng cao', 'code' => 'VATLYNC'],
            ['name' => 'Hóa học nâng cao', 'code' => 'HOAHOCNC'],
            ['name' => 'Sinh học nâng cao', 'code' => 'SINHNC'],
            ['name' => 'Ngữ văn nâng cao', 'code' => 'NGUVANNC'],
            ['name' => 'Lịch sử nâng cao', 'code' => 'LSNC'],
            ['name' => 'Địa lý nâng cao', 'code' => 'DLNC'],
            
            // Môn học tự chọn
            ['name' => 'Tiếng Pháp', 'code' => 'TP'],
            ['name' => 'Tiếng Nhật', 'code' => 'TJ'],
            ['name' => 'Tiếng Trung', 'code' => 'TT'],
            ['name' => 'Tiếng Hàn', 'code' => 'TH'],
            ['name' => 'Khoa học máy tính', 'code' => 'KHTM'],
            ['name' => 'Kinh tế', 'code' => 'KT'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['code' => $subject['code']],
                ['name' => $subject['name']]
            );
        }

        $this->command->info('Đã tạo ' . count($subjects) . ' môn học.');
    }
}

