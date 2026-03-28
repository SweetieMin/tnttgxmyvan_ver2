<?php

use App\Livewire\Admin\Settings\Site\GeneralSettings;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDetail;
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
    $user = User::factory()->create([
        'christian_name' => 'Anna',
        'last_name' => 'Nguyễn Kiều Khánh',
        'name' => 'Thy',
        'token' => str_repeat('a', 64),
    ]);
    $user->givePermissionTo('settings.site.general.view');

    $response = $this->actingAs($user)->get(route('admin.settings.site.general'));

    $response->assertOk()
        ->assertSeeText(__('System configuration'))
        ->assertSeeText(__('Badge card configuration'))
        ->assertSeeText(__('Preview badge'))
        ->assertSeeText(__('Link width and height'))
        ->assertSeeText('Anna')
        ->assertSeeText('Nguyễn Kiều Khánh Thy');
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

test('badge template settings can be updated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'settings.site.general.view',
        'settings.site.general.update',
    ]);

    $this->actingAs($user);

    $badgeLayout = json_encode([
        'logo' => ['x' => 4, 'y' => 4, 'w' => 14, 'h' => 14],
        'heading' => ['x' => 18, 'y' => 4, 'w' => 68, 'h' => 15],
        'qr' => ['x' => 28, 'y' => 24, 'w' => 44, 'h' => 28],
        'name_panel' => ['x' => 3, 'y' => 74, 'w' => 94, 'h' => 20],
        'avatar' => ['x' => 24, 'y' => 54, 'w' => 52, 'h' => 27],
        'christian_name' => ['x' => 22, 'y' => 84, 'w' => 56, 'h' => 6],
        'full_name' => ['x' => 14, 'y' => 90, 'w' => 72, 'h' => 7],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    Livewire::test(GeneralSettings::class)
        ->set('badge_title', 'Doan TNTT Giao Xu My Van')
        ->set('badge_subtitle', 'Xu Doan Giuse Vien')
        ->set('badge_background_color', '#fff0aa')
        ->set('badge_name_panel_color', '#e6c46e')
        ->set('badge_layout', $badgeLayout)
        ->call('updateBadgeTemplateSettings')
        ->assertHasNoErrors();

    expect(Setting::query()->where('key', 'badge.title')->value('value'))->toBe('Doan TNTT Giao Xu My Van');
    expect(Setting::query()->where('key', 'badge.subtitle')->value('value'))->toBe('Xu Doan Giuse Vien');
    expect(Setting::query()->where('key', 'badge.background_color')->value('value'))->toBe('#fff0aa');
    expect(Setting::query()->where('key', 'badge.name_panel_color')->value('value'))->toBe('#e6c46e');
    expect(Setting::query()->where('key', 'badge.layout')->value('value'))->toBe($badgeLayout);
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

test('badge template save button only appears after the form changes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.general.update');

    $this->actingAs($user);

    Livewire::test(GeneralSettings::class)
        ->call('hasBadgeTemplateChanges')
        ->assertReturned(false)
        ->set('badge_title', 'Doan TNTT Giao Xu My Van')
        ->call('hasBadgeTemplateChanges')
        ->assertReturned(true);
});

test('badge preview can be exported as png', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'christian_name' => 'Anna',
        'last_name' => 'Nguyễn Ly',
        'name' => 'Na',
        'token' => str_repeat('b', 64),
    ]);
    $user->givePermissionTo([
        'settings.site.general.view',
        'settings.site.general.update',
    ]);

    $avatarFile = UploadedFile::fake()->image('anna.png', 300, 300);
    $storedAvatarPath = $avatarFile->storeAs('images/users', 'anna-avatar.png', 'public');

    UserDetail::query()->updateOrCreate(
        ['user_id' => $user->id],
        ['picture' => basename($storedAvatarPath)],
    );

    $this->actingAs($user);

    Livewire::test(GeneralSettings::class)
        ->call('exportBadgePreviewPng')
        ->assertFileDownloaded('badge-preview.png', content: null, contentType: 'image/png');
});

test('badge preview favicon resolves from branding favicon setting', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.general.view');

    Setting::query()->updateOrCreate(
        ['key' => 'branding.favicon'],
        [
            'group' => 'branding',
            'type' => 'image',
            'label' => 'Favicon',
            'value' => 'images/sites/FAVICON-preview.png',
            'description' => null,
            'is_public' => true,
            'is_encrypted' => false,
            'autoload' => true,
            'sort_order' => 120,
        ],
    );

    $this->actingAs($user);

    expect(Livewire::test(GeneralSettings::class)->instance()->previewSiteFaviconUrl())
        ->toEndWith('/storage/images/sites/FAVICON-preview.png');
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
