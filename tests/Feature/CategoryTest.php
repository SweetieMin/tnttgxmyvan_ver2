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
        ->pluck('name')
        ->all())
        ->toBe([
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
        ]);
});
