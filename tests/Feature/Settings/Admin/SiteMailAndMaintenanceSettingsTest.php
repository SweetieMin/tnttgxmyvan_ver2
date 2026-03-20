<?php

use App\Livewire\Admin\Settings\Site\MailSettings;
use App\Livewire\Admin\Settings\Site\MaintenanceSettings;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'settings.site.email.update',
        'settings.site.maintenance.update',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('mail settings can be updated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.email.update');

    $this->actingAs($user);

    Livewire::test(MailSettings::class)
        ->set('from_address', 'mailer@example.com')
        ->set('from_name', 'Doan TNTT Gx My Van')
        ->set('reply_to_address', 'reply@example.com')
        ->set('username', 'mailer@example.com')
        ->set('password', 'secret-password')
        ->set('mailer', 'smtp')
        ->set('host', 'smtp.example.com')
        ->set('encryption', 'tls')
        ->set('port', 587)
        ->call('updateEmailSettings')
        ->assertHasNoErrors();

    expect(Setting::query()->where('key', 'mail.from_address')->value('value'))->toBe('mailer@example.com');
    expect(Setting::query()->where('key', 'mail.from_name')->value('value'))->toBe('Doan TNTT Gx My Van');
    expect(Setting::query()->where('key', 'mail.reply_to_address')->value('value'))->toBe('reply@example.com');
    expect(Setting::query()->where('key', 'mail.username')->value('value'))->toBe('mailer@example.com');
    expect(Setting::query()->where('key', 'mail.host')->value('value'))->toBe('smtp.example.com');

    $rawPassword = Setting::query()->toBase()->where('key', 'mail.password')->value('value');

    expect($rawPassword)->not->toBe('secret-password');
    expect(Crypt::decryptString($rawPassword))->toBe('secret-password');
    expect(Setting::query()->where('key', 'mail.password')->first()?->value)->toBe('secret-password');
});

test('maintenance settings can be updated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.maintenance.update');

    $this->actingAs($user);

    Livewire::test(MaintenanceSettings::class)
        ->set('is_maintenance', true)
        ->set('message', 'System maintenance in progress.')
        ->set('start_at', '2026-03-21T08:00')
        ->set('end_at', '2026-03-21T10:00')
        ->call('updateMaintenanceSettings')
        ->assertHasNoErrors();

    expect(Setting::query()->where('key', 'maintenance.enabled')->value('value'))->toBe('1');
    expect(Setting::query()->where('key', 'maintenance.message')->value('value'))->toBe('System maintenance in progress.');
    expect(Setting::query()->where('key', 'maintenance.start_at')->value('value'))->toBe('2026-03-21T08:00');
    expect(Setting::query()->where('key', 'maintenance.end_at')->value('value'))->toBe('2026-03-21T10:00');
    expect(Setting::query()->where('key', 'maintenance.secret_key')->value('value'))->not->toBe('');
});

test('enabling maintenance requires confirmation before taking the app down', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.maintenance.update');

    $this->actingAs($user);

    Artisan::shouldReceive('call')
        ->once()
        ->withArgs(fn (string $command): bool => str_starts_with($command, 'down --secret='));

    $component = Livewire::test(MaintenanceSettings::class)
        ->set('is_maintenance', true)
        ->set('message', 'System maintenance in progress.')
        ->set('start_at', '08:00')
        ->set('end_at', '12:00')
        ->call('updateMaintenanceSettings')
        ->assertHasNoErrors();

    $secretKey = $component->get('secret_key');

    $component
        ->call('enableMaintenanceConfirm')
        ->assertRedirect("/{$secretKey}");
});

test('maintenance mode generates an ordered secret key and default schedule when enabled', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.maintenance.update');

    $this->actingAs($user);

    $component = Livewire::test(MaintenanceSettings::class)
        ->set('is_maintenance', true);

    $secretKey = $component->get('secret_key');
    $startAt = $component->get('start_at');
    $endAt = $component->get('end_at');

    expect(Str::isUuid($secretKey))->toBeTrue();
    expect($startAt)->toMatch('/^\d{2}:\d{2}$/');
    expect($endAt)->toMatch('/^\d{2}:\d{2}$/');
});

test('disabling maintenance clears maintenance settings and brings the app back online', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.maintenance.update');

    Setting::query()->updateOrCreate(
        ['key' => 'maintenance.enabled'],
        [
            'group' => 'maintenance',
            'value' => '1',
            'type' => 'boolean',
            'label' => 'Bật bảo trì',
            'description' => 'Trạng thái bật hoặc tắt chế độ bảo trì của hệ thống.',
            'is_public' => false,
            'is_encrypted' => false,
            'autoload' => true,
            'sort_order' => 180,
        ],
    );

    foreach ([
        'maintenance.secret_key' => 'secret-key',
        'maintenance.message' => 'Maintenance message',
        'maintenance.start_at' => '08:00',
        'maintenance.end_at' => '12:00',
    ] as $key => $value) {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => 'maintenance',
                'value' => $value,
                'type' => 'string',
                'label' => $key,
                'description' => $key,
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 999,
            ],
        );
    }

    $this->actingAs($user);

    Artisan::shouldReceive('call')
        ->once()
        ->with('up');

    Livewire::test(MaintenanceSettings::class)
        ->set('app_is_in_maintenance', true)
        ->set('is_maintenance', false)
        ->call('updateMaintenanceSettings')
        ->assertHasNoErrors()
        ->assertSet('secret_key', '')
        ->assertSet('message', '')
        ->assertSet('start_at', '')
        ->assertSet('end_at', '');

    expect(Setting::query()->where('key', 'maintenance.enabled')->value('value'))->toBe('0');
    expect(Setting::query()->where('key', 'maintenance.secret_key')->value('value'))->toBe('');
    expect(Setting::query()->where('key', 'maintenance.message')->value('value'))->toBe('');
    expect(Setting::query()->where('key', 'maintenance.start_at')->value('value'))->toBe('');
    expect(Setting::query()->where('key', 'maintenance.end_at')->value('value'))->toBe('');
});
