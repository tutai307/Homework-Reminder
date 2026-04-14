<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\Homework;
use App\Models\HomeworkItem;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Bắt đầu khởi tạo dữ liệu Demo thực tế...');

        // 1. Lấy danh sách lớp hiện tại (Tối đa 16 lớp)
        $classes = ClassModel::take(16)->get();
        if ($classes->isEmpty()) {
            $this->command->error('Không tìm thấy lớp nào trong DB. Vui lòng tạo lớp trước hoặc chạy DatabaseSeeder cơ bản.');
            return;
        }

        // 2. Tạo 8 Giáo viên mới
        $teacherNames = [
            'Nguyễn Thị Lan', 'Trần Văn Tuấn', 'Lê Hữu Hoàng', 'Phạm Quỳnh Như',
            'Hoàng Thị Ngọc', 'Đinh Văn Nam', 'Đoàn Thanh Hải', 'Bùi Bích Phương'
        ];

        $teachers = [];
        $password = Hash::make('password');
        
        foreach ($teacherNames as $index => $name) {
            $email = 'gv.' . str()->slug($name, '') . '@school.edu.vn';
            $teacher = User::firstOrCreate([
                'email' => $email
            ], [
                'name' => $name,
                'password' => $password,
                'role' => 'teacher'
            ]);
            $teachers[] = $teacher;
        }

        // Phân bổ lớp cho Giáo viên: Mỗi GV phụ trách ít nhất 2 lớp (tùy số lượng lớp có sẵn)
        $this->command->info('Phân bổ lớp cho giáo viên...');
        $classChunks = $classes->chunk(ceil($classes->count() / count($teachers)));
        foreach ($teachers as $i => $teacher) {
            $assignedClasses = $classChunks->get($i) ?? collect();
            if ($assignedClasses->isNotEmpty()) {
                $teacher->classes()->syncWithoutDetaching($assignedClasses->pluck('id'));
            }
        }

        // 3. Tạo 32 Học sinh Ban cán sự
        $this->command->info('Đang tạo tài khoản Ban cán sự...');
        foreach ($classes as $class) {
            $monitor = User::firstOrCreate([
                'email' => 'lopthuong.' . str()->slug($class->name, '') . '@school.edu.vn'
            ], [
                'name' => 'LT ' . $class->name,
                'password' => $password,
                'role' => 'class_monitor'
            ]);
            $monitor->classes()->syncWithoutDetaching([$class->id]);

            $subMonitor = User::firstOrCreate([
                'email' => 'loppho.' . str()->slug($class->name, '') . '@school.edu.vn'
            ], [
                'name' => 'LP ' . $class->name,
                'password' => $password,
                'role' => 'academic_sub_monitor'
            ]);
            $subMonitor->classes()->syncWithoutDetaching([$class->id]);
        }

        // 4. Kiểm tra Timetable và clone nếu thiếu
        $this->command->info('Kiểm tra và nhân bản Thời khóa biểu...');
        $firstClassWithTimetable = ClassModel::whereHas('timetables')->first();
        if ($firstClassWithTimetable) {
            $sampleTimetables = Timetable::where('class_id', $firstClassWithTimetable->id)->get();
            foreach ($classes as $class) {
                $count = Timetable::where('class_id', $class->id)->count();
                if ($count === 0) {
                    foreach ($sampleTimetables as $st) {
                        Timetable::create([
                            'class_id' => $class->id,
                            'subject_id' => $st->subject_id,
                            'weekday' => $st->weekday,
                            'period' => $st->period,
                        ]);
                    }
                }
            }
        } else {
            $this->command->warn('Không có lớp nào có sẵn Thời khóa biểu. Bạn cần tự tạo TKB tay cho ít nhất 1 lớp.');
        }

        // 5. Trích xuất kho bài tập thực tế từ trong DB
        $this->command->info('Chuẩn bị rải bài tập thực tế...');
        $subjects = Subject::all();
        $sampleHomeworks = collect();
        
        foreach ($subjects as $subject) {
            $items = HomeworkItem::where('subject_id', $subject->id)
                                 ->whereNotNull('content')
                                 ->where('content', '!=', '')
                                 ->limit(20)
                                 ->get();
            if ($items->isNotEmpty()) {
                $sampleHomeworks->put($subject->id, $items->pluck('content')->toArray());
            } else {
                $sampleHomeworks->put($subject->id, ['Làm bài tập SGK', 'Chuẩn bị bài mới']);
            }
        }

        // Bắt đầu rải bài tập cho 5 ngày qua và hôm nay
        $today = Carbon::today();
        
        foreach ($classes as $class) {
            $assignedTeacher = User::whereHas('classes', function($q) use ($class) {
                $q->where('classes.id', $class->id);
            })->where('role', 'teacher')->first();
            
            $assignedTeacherId = $assignedTeacher ? $assignedTeacher->id : User::where('role', 'admin')->first()->id;
            
            $monitor = User::whereHas('classes', function($q) use ($class) {
                $q->where('classes.id', $class->id);
            })->where('role', 'class_monitor')->first();
            $monitorId = $monitor ? $monitor->id : null;

            $subMonitor = User::whereHas('classes', function($q) use ($class) {
                $q->where('classes.id', $class->id);
            })->where('role', 'academic_sub_monitor')->first();

            for ($daysBack = 5; $daysBack >= 0; $daysBack--) {
                $targetDate = $today->copy()->subDays($daysBack);
                $weekday = $targetDate->dayOfWeek;
                $dbWeekday = $weekday == 0 ? 7 : $weekday;

                $classSubjects = Timetable::where('class_id', $class->id)
                    ->where('weekday', $dbWeekday)
                    ->pluck('subject_id')
                    ->unique();

                if ($classSubjects->isEmpty()) continue;

                // 85% cơ hội ngày đó có báo bài
                if (rand(1, 100) > 85) continue;

                // Determine who creates the homework (85% Monitor, 15% Teacher)
                $isMonitorAction = rand(1, 100) <= 85 && $monitorId;
                $creatorId = $isMonitorAction ? $monitorId : $assignedTeacherId;

                $homework = Homework::firstOrCreate([
                    'class_id' => $class->id,
                    'date' => $targetDate->format('Y-m-d')
                ], [
                    'created_by' => $creatorId,
                    'notes' => rand(1, 10) > 7 ? 'Nhớ làm đầy đủ nhé!' : null
                ]);

                // Log: Tạo bài tập
                ActivityLog::create([
                    'user_id' => $creatorId,
                    'class_id' => $class->id,
                    'action' => 'create_homework',
                    'description' => 'đã cập nhật bài tập hàng ngày',
                    'created_at' => $targetDate->copy()->addHours(rand(10, 16))->addMinutes(rand(1,59)),
                ]);

                // Giả lập thỉnh thoảng (20%) Giáo viên vào chỉnh sửa hoặc thêm ghi chú
                if ($isMonitorAction && rand(1, 100) <= 20) {
                    ActivityLog::create([
                        'user_id' => $assignedTeacherId,
                        'class_id' => $class->id,
                        'action' => 'update_homework',
                        'description' => 'đã kiểm tra và chỉnh sửa bài tập',
                        'created_at' => $targetDate->copy()->addHours(rand(16, 18))->addMinutes(rand(1,59)),
                    ]);
                }

                foreach ($classSubjects as $subjectId) {
                    if (rand(1, 100) > 20) { // 80% có bài
                        $contents = $sampleHomeworks->get($subjectId);
                        $content = $contents[array_rand($contents)];
                        
                        HomeworkItem::firstOrCreate([
                            'homework_id' => $homework->id,
                            'subject_id' => $subjectId,
                        ], [
                            'content' => $content,
                            'due_date' => $targetDate->copy()->addDays(rand(1, 4))->format('Y-m-d')
                        ]);
                    }
                }

                // Log: Lớp phó hoặc HS khác xem/xác nhận
                if ($subMonitor && rand(1, 100) > 30) {
                    ActivityLog::create([
                        'user_id' => $subMonitor->id,
                        'class_id' => $class->id,
                        'action' => 'view_homework',
                        'description' => 'đã xem và báo cáo TKB',
                        'created_at' => $targetDate->copy()->addHours(rand(17, 21))->addMinutes(rand(1,59)),
                    ]);
                }
            }

            // Giả lập lịch sử Giáo viên quản lý TKB và Ban cán sự 
            // (1 log mỗi lớp trong 5 ngày qua)
            if (rand(1, 2) == 1) {
                ActivityLog::create([
                    'user_id' => $assignedTeacherId,
                    'class_id' => $class->id,
                    'action' => 'update_timetable',
                    'description' => 'đã chỉnh sửa thời khóa biểu',
                    'created_at' => $today->copy()->subDays(rand(1, 4))->addHours(rand(8, 11)),
                ]);
            }
        }

        $this->command->info('Dữ liệu Demo đã được tạo thành công! Sẵn sàng diễn tập.');
    }
}
