<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
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

test('encrypted settings are stored encrypted and read back decrypted', function () {
    $setting = Setting::query()->create([
        'group' => 'mail',
        'key' => 'mail.password',
        'value' => 'secret-password',
        'type' => 'string',
        'label' => 'SMTP password',
        'description' => 'Encrypted SMTP password.',
        'is_public' => false,
        'is_encrypted' => true,
        'autoload' => false,
        'sort_order' => 1,
    ]);

    $rawValue = Setting::query()
        ->toBase()
        ->where('id', $setting->id)
        ->value('value');

    expect($rawValue)->not->toBe('secret-password')
        ->and(Crypt::decryptString($rawValue))->toBe('secret-password')
        ->and($setting->fresh()->value)->toBe('secret-password');
});
