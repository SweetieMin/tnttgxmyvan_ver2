<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

test('settings table contains the expected system setting columns', function () {
    expect(Schema::hasTable('settings'))->toBeTrue()
        ->and(Schema::hasColumns('settings', [
            'group',
            'key',
            'value',
            'type',
            'label',
            'description',
            'is_public',
            'is_encrypted',
            'autoload',
            'sort_order',
        ]))->toBeTrue();
});

test('setting model casts boolean and integer fields correctly', function () {
    $setting = Setting::factory()->create([
        'is_public' => 1,
        'is_encrypted' => 0,
        'autoload' => 1,
        'sort_order' => '10',
    ]);

    expect($setting->is_public)->toBeTrue()
        ->and($setting->is_encrypted)->toBeFalse()
        ->and($setting->autoload)->toBeTrue()
        ->and($setting->sort_order)->toBeInt()
        ->and($setting->sort_order)->toBe(10);
});
