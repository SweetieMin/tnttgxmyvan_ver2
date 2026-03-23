<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Bổn Mạng',
            'Trung Thu',
            'Giáng Sinh',
            'Tết niên',
            'Tết Nguyên Đán',
            'Tĩnh Tâm',
            'Ngày của Cha & Mẹ',
            'Trại Ấi',
            'Trại Thiếu',
            'Đá bóng',
            'Du lịch',
            'Hỗ trợ trại huấn luyện',
        ];

        foreach ($categories as $index => $name) {
            Category::query()->updateOrCreate([
                'name' => $name,
            ], [
                'ordering' => $index + 1,
                'description' => null,
                'is_active' => true,
            ]);
        }
    }
}
