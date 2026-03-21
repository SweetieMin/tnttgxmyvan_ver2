<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            ['course' => 'Khai Tâm 1', 'sector' => 'Tiền Ấu 1'],   // 1
            ['course' => 'Khai Tâm 2', 'sector' => 'Tiền Ấu 2'],   // 2
            ['course' => 'Khai Tâm 3', 'sector' => 'Tiền Ấu 3'], // 3
            ['course' => 'Xưng Tội 1', 'sector' => 'Ấu 1'],   // 4
            ['course' => 'Xưng Tội 2', 'sector' => 'Ấu 2'],   // 5
            ['course' => 'Thêm Sức 1', 'sector' => 'Ấu 3'],   // 6
            ['course' => 'Thêm Sức 2', 'sector' => 'Thiếu 1'],   // 7
            ['course' => 'Thêm Sức 3', 'sector' => 'Thiếu 2'],   // 8
            ['course' => 'Kinh Thánh 1', 'sector' => 'Thiếu 3'],   // 9
            ['course' => 'Kinh Thánh 2', 'sector' => 'Nghĩa 1'],   // 10
            ['course' => 'Kinh Thánh 3', 'sector' => 'Nghĩa 2'],   // 11
            ['course' => 'Vào Đời', 'sector' => 'Nghĩa 3'],   // 12
        ];

        $ordering = 1;

        foreach ($programs as $program) {
            Program::query()->updateOrCreate([
                'course' => $program['course'],
            ], [
                'ordering' => $ordering++,
                'course' => $program['course'],
                'sector' => $program['sector'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
