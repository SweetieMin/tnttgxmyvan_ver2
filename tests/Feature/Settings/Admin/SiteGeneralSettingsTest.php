<?php

use App\Livewire\Admin\Settings\Site\GeneralSettings;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'settings.site.general.view',
        'settings.site.general.update',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the general settings page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.general.view');

    $response = $this->actingAs($user)->get(route('admin.settings.site.general'));

    $response->assertOk()
        ->assertSeeText(__('System configuration'));
});

test('general settings can be updated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'settings.site.general.view',
        'settings.site.general.update',
    ]);

    $this->actingAs($user);

    Livewire::test(GeneralSettings::class)
        ->set('site_title', 'My Van Parish Youth')
        ->set('site_email', 'contact@example.com')
        ->set('site_phone', '0909000111')
        ->set('site_meta_keywords', 'youth, parish')
        ->set('site_meta_description', 'A parish youth management system.')
        ->set('facebook_url', 'https://facebook.com/myvan')
        ->set('instagram_url', 'https://instagram.com/myvan')
        ->set('youtube_url', 'https://youtube.com/@myvan')
        ->set('tikTok_url', 'https://www.tiktok.com/@myvan')
        ->call('updateGeneralSettings')
        ->assertHasNoErrors();

    expect(Setting::query()->where('key', 'general.site_name')->value('value'))->toBe('My Van Parish Youth');
    expect(Setting::query()->where('key', 'general.site_email')->value('value'))->toBe('contact@example.com');
    expect(Setting::query()->where('key', 'general.site_phone')->value('value'))->toBe('0909000111');
    expect(Setting::query()->where('key', 'general.meta_keywords')->value('value'))->toBe('youth, parish');
    expect(Setting::query()->where('key', 'general.meta_description')->value('value'))->toBe('A parish youth management system.');
    expect(Setting::query()->where('key', 'social.facebook_url')->value('value'))->toBe('https://facebook.com/myvan');
    expect(Setting::query()->where('key', 'social.instagram_url')->value('value'))->toBe('https://instagram.com/myvan');
    expect(Setting::query()->where('key', 'social.youtube_url')->value('value'))->toBe('https://youtube.com/@myvan');
    expect(Setting::query()->where('key', 'social.tiktok_url')->value('value'))->toBe('https://www.tiktok.com/@myvan');
});

test('logo, favicon, and login image can be uploaded and removed', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo([
        'settings.site.general.view',
        'settings.site.general.update',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(GeneralSettings::class)
        ->set('site_logo', UploadedFile::fake()->image('logo.png'))
        ->call('saveLogo')
        ->set('site_favicon', UploadedFile::fake()->image('favicon.png'))
        ->call('saveFavicon')
        ->set('site_login_image', UploadedFile::fake()->image('login-image.png'))
        ->call('saveLoginImage')
        ->assertHasNoErrors();

    $logoPath = Setting::query()->where('key', 'branding.logo')->value('value');
    $faviconPath = Setting::query()->where('key', 'branding.favicon')->value('value');
    $loginImagePath = Setting::query()->where('key', 'branding.login_image')->value('value');

    expect($logoPath)->not->toBeNull();
    expect($faviconPath)->not->toBeNull();
    expect($loginImagePath)->not->toBeNull();
    expect($logoPath)->toStartWith('images/sites/LOGO-');
    expect($faviconPath)->toStartWith('images/sites/FAVICON-');
    expect($loginImagePath)->toStartWith('images/sites/LOGIN-');

    Storage::disk('public')->assertExists($logoPath);
    Storage::disk('public')->assertExists($faviconPath);
    Storage::disk('public')->assertExists($loginImagePath);

    $component
        ->set('site_logo', UploadedFile::fake()->image('logo-second.png'))
        ->call('saveLogo')
        ->set('site_favicon', UploadedFile::fake()->image('favicon-second.png'))
        ->call('saveFavicon')
        ->set('site_login_image', UploadedFile::fake()->image('login-image-second.png'))
        ->call('saveLoginImage')
        ->assertHasNoErrors();

    $updatedLogoPath = Setting::query()->where('key', 'branding.logo')->value('value');
    $updatedFaviconPath = Setting::query()->where('key', 'branding.favicon')->value('value');
    $updatedLoginImagePath = Setting::query()->where('key', 'branding.login_image')->value('value');

    expect($updatedLogoPath)->not->toBe($logoPath);
    expect($updatedFaviconPath)->not->toBe($faviconPath);
    expect($updatedLoginImagePath)->not->toBe($loginImagePath);

    Storage::disk('public')->assertMissing($logoPath);
    Storage::disk('public')->assertMissing($faviconPath);
    Storage::disk('public')->assertMissing($loginImagePath);
    Storage::disk('public')->assertExists($updatedLogoPath);
    Storage::disk('public')->assertExists($updatedFaviconPath);
    Storage::disk('public')->assertExists($updatedLoginImagePath);

    $component
        ->call('removeLogo')
        ->call('deleteConfirm')
        ->call('removeFavicon')
        ->call('deleteConfirm')
        ->call('removeLoginImage')
        ->call('deleteConfirm')
        ->assertHasNoErrors();

    expect(Setting::query()->where('key', 'branding.logo')->value('value'))->toBeNull();
    expect(Setting::query()->where('key', 'branding.favicon')->value('value'))->toBeNull();
    expect(Setting::query()->where('key', 'branding.login_image')->value('value'))->toBeNull();
    Storage::disk('public')->assertMissing($updatedLogoPath);
    Storage::disk('public')->assertMissing($updatedFaviconPath);
    Storage::disk('public')->assertMissing($updatedLoginImagePath);
});

test('selected tab is restored from the query string', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.general.view');

    $this->actingAs($user);

    Livewire::withQueryParams(['tab' => 'logo-favicon'])
        ->test(GeneralSettings::class)
        ->assertSet('tab', 'logo-favicon');
});

test('general settings save button only appears after the form changes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.general.update');

    $this->actingAs($user);

    Livewire::test(GeneralSettings::class)
        ->call('hasGeneralChanges')
        ->assertReturned(false)
        ->set('site_title', 'My Van Parish')
        ->call('hasGeneralChanges')
        ->assertReturned(true);
});
