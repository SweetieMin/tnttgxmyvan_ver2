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
            'Bổn Mạng' => 'Chi phí cho lễ bổn mạng và các hoạt động liên quan lễ bổn mạng 21/08 hằng năm.',
            'Trung Thu' => 'Chi phí tổ chức chương trình và quà tặng dịp Trung Thu.',
            'Giáng Sinh' => 'Chi phí trang trí, quà tặng và sinh hoạt dịp Giáng Sinh.',
            'Tết niên' => 'Chi phí tổ chức chương trình tất niên và họp mặt cuối năm.',
            'Tết Nguyên Đán' => 'Thu/Chi cho các hoạt động mừng Tết Nguyên Đán.',
            'Tĩnh Tâm' => 'Chi phí cho các buổi tĩnh tâm, cầu nguyện và sinh hoạt thiêng liêng.',
            'Ngày của Cha & Mẹ' => 'Chi phí quà tặng và chương trình tri ân cha mẹ.',
            'Trại Ấu' => 'Chi phí tổ chức trại và sinh hoạt dành cho ngành Ấu.',
            'Trại Thiếu' => 'Chi phí tổ chức trại và sinh hoạt dành cho ngành Thiếu.',
            'Đá bóng' => 'Chi phí sân bãi, dụng cụ và tổ chức hoạt động bóng đá.',
            'Du lịch' => 'Chi phí xe cộ, ăn uống và tổ chức các chuyến tham quan.',
            'Hỗ trợ trại huấn luyện' => 'Chi phí hỗ trợ các khóa trại và huấn luyện viên.',
        ];

        $ordering = 1;

        foreach ($categories as $name => $description) {
            Category::query()->updateOrCreate([
                'name' => $name,
            ], [
                'ordering' => $ordering,
                'description' => $description,
                'is_active' => true,
            ]);

            $ordering++;
        }
    }
}
