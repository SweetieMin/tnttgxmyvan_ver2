<?php

use App\Models\User;
use Database\Seeders\PersonnelRosterSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('personnel roster seeder creates the requested roster counts by role', function () {
    $this->seed(UserSeeder::class);
    $this->seed(RoleSeeder::class);
    $this->seed(PersonnelRosterSeeder::class);

    expect(User::role('Cha Tuyên Úy')->count())->toBe(1)
        ->and(User::role('Thầy Phó Tế')->count())->toBe(1)
        ->and(User::role('Trưởng Giáo Lý')->count())->toBe(1)
        ->and(User::role('Phó Giáo Lý')->count())->toBe(1)
        ->and(User::role('Giáo Lý Viên')->count())->toBe(12)
        ->and(User::role('Xứ Đoàn Trưởng')->count())->toBe(1)
        ->and(User::role('Xứ Đoàn Phó')->count())->toBe(1)
        ->and(User::role('Trưởng Ngành Nghĩa')->count())->toBe(1)
        ->and(User::role('Phó Ngành Nghĩa')->count())->toBe(1)
        ->and(User::role('Trưởng Ngành Thiếu')->count())->toBe(1)
        ->and(User::role('Phó Ngành Thiếu')->count())->toBe(1)
        ->and(User::role('Trưởng Ngành Ấu')->count())->toBe(1)
        ->and(User::role('Phó Ngành Ấu')->count())->toBe(1)
        ->and(User::role('Trưởng Ngành Tiền Ấu')->count())->toBe(1)
        ->and(User::role('Phó Ngành Tiền Ấu')->count())->toBe(1)
        ->and(User::role('Huynh Trưởng')->count())->toBe(12)
        ->and(User::role('Dự Trưởng')->count())->toBe(12)
        ->and(User::role('Thiếu Nhi')->count())->toBe(24);
});

test('personnel roster seeder assigns the expected existing named users to leadership roles', function () {
    $this->seed(UserSeeder::class);
    $this->seed(RoleSeeder::class);
    $this->seed(PersonnelRosterSeeder::class);

    expect(User::query()->where('username', 'MV01109999')->firstOrFail()->roles->pluck('name')->all())->toBe(['Xứ Đoàn Trưởng'])
        ->and(User::query()->where('username', 'MV19089999')->firstOrFail()->roles->pluck('name')->all())->toBe(['Xứ Đoàn Phó'])
        ->and(User::query()->where('username', 'MV21099898')->firstOrFail()->roles->pluck('name')->all())->toBe(['Trưởng Ngành Nghĩa'])
        ->and(User::query()->where('username', 'MV26089924')->firstOrFail()->roles->pluck('name')->all())->toBe(['Phó Ngành Nghĩa'])
        ->and(User::query()->where('username', 'MV19019797')->firstOrFail()->roles->pluck('name')->all())->toBe(['Trưởng Ngành Thiếu'])
        ->and(User::query()->where('username', 'MV03010574')->firstOrFail()->roles->pluck('name')->all())->toBe(['Phó Ngành Thiếu'])
        ->and(User::query()->where('username', 'MV26030101')->firstOrFail()->roles->pluck('name')->all())->toBe(['Trưởng Ngành Ấu'])
        ->and(User::query()->where('username', 'MV01070274')->firstOrFail()->roles->pluck('name')->all())->toBe(['Phó Ngành Ấu'])
        ->and(User::query()->where('username', 'MV11100101')->firstOrFail()->roles->pluck('name')->all())->toBe(['Trưởng Ngành Tiền Ấu'])
        ->and(User::query()->where('username', 'MV14090524')->firstOrFail()->roles->pluck('name')->all())->toBe(['Trưởng Giáo Lý'])
        ->and(User::query()->where('username', 'MV22109686')->firstOrFail()->roles->pluck('name')->all())->toBe(['Phó Giáo Lý']);
});
