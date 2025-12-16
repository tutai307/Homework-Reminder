<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các quyền cơ bản
        $permissions = [
            'manage-classes',
            'manage-subjects',
            'manage-users',
            'manage-roles',
            'manage-permissions',
            'create-timetable',
            'create-homework',
            'view-homework',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Tạo các vai trò với guard 'web'
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $classMonitorRole = Role::firstOrCreate(['name' => 'class_monitor', 'guard_name' => 'web']);

        // Gán quyền cho admin (full quyền)
        $adminRole->syncPermissions($permissions);

        // Gán quyền cho teacher (tạo thời khóa biểu và bài tập)
        $teacherRole->syncPermissions([
            'create-timetable',
            'create-homework',
            'view-homework',
        ]);

        // Gán quyền cho lớp trưởng (chỉ tạo bài tập)
        $classMonitorRole->syncPermissions([
            'create-homework',
            'view-homework',
        ]);

        // Tạo tài khoản Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản trị viên',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $admin->assignRole('admin');

        // Tạo tài khoản Giáo viên
        $teacher = User::updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Giáo viên',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ]
        );
        $teacher->assignRole('teacher');

        // Tạo tài khoản Lớp trưởng mẫu
        $classMonitor = User::updateOrCreate(
            ['email' => 'monitor@example.com'],
            [
                'name' => 'Lớp trưởng',
                'password' => Hash::make('password'),
                'role' => 'class_monitor',
            ]
        );
        $classMonitor->assignRole('class_monitor');

        $this->command->info('Đã tạo 3 tài khoản:');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Teacher: teacher@example.com / password');
        $this->command->info('Class Monitor: monitor@example.com / password');
        $this->command->info('Đã tạo ' . count($permissions) . ' quyền và 3 vai trò.');
    }
}

