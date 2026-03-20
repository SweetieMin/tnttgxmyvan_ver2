<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [

            ['name' => 'Cha Tuyên Úy'],
            ['name' => 'Thầy Phó Tế'],

            ['name' => 'Trưởng Giáo Lý'],
            ['name' => 'Phó Giáo Lý'],
            ['name' => 'Giáo Lý Viên'],

            ['name' => 'Xứ Đoàn Trưởng'],
            ['name' => 'Xứ Đoàn Phó'],
            ['name' => 'Thủ Quỹ'],

            ['name' => 'Trưởng Ngành Nghĩa'],
            ['name' => 'Phó Ngành Nghĩa'],

            ['name' => 'Trưởng Ngành Thiếu'],
            ['name' => 'Phó Ngành Thiếu'],

            ['name' => 'Trưởng Ngành Ấu'],
            ['name' => 'Phó Ngành Ấu'],

            ['name' => 'Trưởng Ngành Tiền Ấu'],
            ['name' => 'Phó Ngành Tiền Ấu'],

            ['name' => 'Huynh Trưởng'],
            ['name' => 'Dự Trưởng'],

            ['name' => 'Đội Trưởng'],
            ['name' => 'Thiếu Nhi'],
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role['name'], 'web');
        }
    }
}
