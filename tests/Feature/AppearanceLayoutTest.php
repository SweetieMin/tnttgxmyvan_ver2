<?php

use App\Models\Setting;
use App\Models\User;

test('app layout renders the saved theme preset and neutral palette before alpine hydrates', function () {
    Setting::query()->create([
        'group' => 'theme',
        'key' => 'theme.preset',
        'value' => 'amber',
        'type' => 'string',
        'label' => 'Mẫu giao diện',
        'description' => 'Mẫu giao diện được chọn để áp dụng màu sắc cho website.',
        'is_public' => true,
        'is_encrypted' => false,
        'autoload' => true,
        'sort_order' => 70,
    ]);

    Setting::query()->create([
        'group' => 'theme',
        'key' => 'theme.neutral_palette',
        'value' => 'stone',
        'type' => 'string',
        'label' => 'Bảng màu trung tính',
        'description' => 'Bảng màu trung tính được dùng cho nền và các tông xám của giao diện.',
        'is_public' => true,
        'is_encrypted' => false,
        'autoload' => true,
        'sort_order' => 75,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-theme="amber"', false)
        ->assertSee('data-neutral-palette="stone"', false)
        ->assertSee('document.documentElement.dataset.theme = themePreset', false)
        ->assertSee('document.documentElement.dataset.neutralPalette = themeNeutralPalette', false);
});

test('auth layout still uses flux appearance toggle controls', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee("\$flux.appearance = \$flux.dark ? 'light' : 'dark'", false);
});
