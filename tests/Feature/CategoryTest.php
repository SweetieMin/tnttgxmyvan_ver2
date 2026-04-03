<?php

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Support\Facades\Schema;

test('categories table contains the expected finance category columns', function () {
    expect(Schema::hasTable('categories'))->toBeTrue()
        ->and(Schema::hasColumns('categories', [
            'ordering',
            'name',
            'description',
            'is_active',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

test('category seeder seeds the expected finance categories in order', function () {
    $this->seed(CategorySeeder::class);

    expect(Category::query()
        ->orderBy('ordering')
        ->get(['name', 'description'])
        ->map(fn (Category $category): array => [
            'name' => $category->name,
            'description' => $category->description,
        ])
        ->all())
        ->toBe([
            [
                'name' => 'Bổn Mạng',
                'description' => 'Chi phí cho lễ bổn mạng và các hoạt động liên quan.',
            ],
            [
                'name' => 'Trung Thu',
                'description' => 'Chi phí tổ chức chương trình và quà tặng dịp Trung Thu.',
            ],
            [
                'name' => 'Giáng Sinh',
                'description' => 'Chi phí trang trí, quà tặng và sinh hoạt dịp Giáng Sinh.',
            ],
            [
                'name' => 'Tết niên',
                'description' => 'Chi phí tổ chức chương trình tất niên và họp mặt cuối năm.',
            ],
            [
                'name' => 'Tết Nguyên Đán',
                'description' => 'Chi phí cho các hoạt động mừng Tết Nguyên Đán.',
            ],
            [
                'name' => 'Tĩnh Tâm',
                'description' => 'Chi phí cho các buổi tĩnh tâm, cầu nguyện và sinh hoạt thiêng liêng.',
            ],
            [
                'name' => 'Ngày của Cha & Mẹ',
                'description' => 'Chi phí quà tặng và chương trình tri ân cha mẹ.',
            ],
            [
                'name' => 'Trại Ấu',
                'description' => 'Chi phí tổ chức trại và sinh hoạt dành cho ngành Ấu.',
            ],
            [
                'name' => 'Trại Thiếu',
                'description' => 'Chi phí tổ chức trại và sinh hoạt dành cho ngành Thiếu.',
            ],
            [
                'name' => 'Đá bóng',
                'description' => 'Chi phí sân bãi, dụng cụ và tổ chức hoạt động bóng đá.',
            ],
            [
                'name' => 'Du lịch',
                'description' => 'Chi phí xe cộ, ăn uống và tổ chức các chuyến tham quan.',
            ],
            [
                'name' => 'Hỗ trợ trại huấn luyện',
                'description' => 'Chi phí hỗ trợ các khóa trại và huấn luyện viên.',
            ],
        ]);
});
