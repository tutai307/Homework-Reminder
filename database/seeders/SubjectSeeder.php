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
            ['name' => 'Sinh hoạt', 'code' => 'SHL'],
            ['name' => 'Khoa học tự nhiên', 'code' => 'KHTN'],
            ['name' => 'Lịch sử và Địa lí', 'code' => 'LSĐL'],
            ['name' => 'Giáo dục công dân', 'code' => 'GDCD'],
            ['name' => 'Công nghệ', 'code' => 'CN'],
            ['name' => 'Tin học', 'code' => 'TIN'],
            ['name' => 'GDTC', 'code' => 'TD'],
            ['name' => 'Nghệ thuật', 'code' => 'NT'],
            ['name' => 'Giáo dục ĐP', 'code' => 'GDĐP'],
            ['name' => 'HĐTN HN', 'code' => 'HDTN'],
            ['name' => 'Toán', 'code' => 'TOAN'],
            ['name' => 'Ngữ văn', 'code' => 'NGUVAN'],
            ['name' => 'Tiếng Anh', 'code' => 'TA'],
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

