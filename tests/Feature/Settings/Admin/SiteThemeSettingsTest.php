<?php

use App\Livewire\Admin\Settings\Site\ThemeSettings;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'settings.site.theme.view',
        'settings.site.theme.update',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the theme settings page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.theme.view');

    $response = $this->actingAs($user)->get(route('admin.settings.site.theme'));

    $response->assertOk()
        ->assertSeeText(__('Theme configuration'));
});

test('theme settings can be updated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'settings.site.theme.view',
        'settings.site.theme.update',
    ]);

    $this->actingAs($user);

    Livewire::test(ThemeSettings::class)
        ->call('selectPreset', 'rose')
        ->set('seasonal_enabled', true)
        ->call('updateThemeSettings')
        ->assertHasNoErrors()
        ->assertSet('preset', 'rose')
        ->assertSet('seasonal_enabled', true);

    expect(Setting::query()->where('key', 'theme.preset')->value('value'))->toBe('rose');
    expect(Setting::query()->where('key', 'theme.seasonal_enabled')->value('value'))->toBe('1');
});
