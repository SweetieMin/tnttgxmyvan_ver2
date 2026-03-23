<?php

use App\Foundation\SiteSettings;
use App\Models\Setting;
use App\Models\User;

test('site settings service returns defaults when settings are missing', function () {
    $siteSettings = app(SiteSettings::class);

    expect($siteSettings->get('theme.preset'))->toBe('sky')
        ->and($siteSettings->get('theme.neutral_palette'))->toBe('gray')
        ->and($siteSettings->theme()->preset())->toBe('sky')
        ->and($siteSettings->branding()->favicon())->toBeNull()
        ->and($siteSettings->branding()->loginImage())->toBeNull()
        ->and($siteSettings->general()->siteName())->toBe('')
        ->and($siteSettings->general()->siteTagline())->toBe('')
        ->and($siteSettings->social()->youtubeUrl())->toBe('')
        ->and($siteSettings->shared())->toMatchArray([
            'themePreset' => 'sky',
            'themeNeutralPalette' => 'gray',
            'themeSeasonalEnabled' => '0',
            'siteTitle' => '',
            'siteTagline' => '',
            'siteMetaKeywords' => '',
            'siteMetaDescription' => '',
            'siteFavicon' => null,
            'siteLoginImage' => null,
            'siteFacebookUrl' => '',
            'siteInstagramUrl' => '',
            'siteYoutubeUrl' => '',
            'siteTikTokUrl' => '',
        ]);
});

test('site settings service loads autoloaded settings and ignores non autoloaded entries', function () {
    Setting::factory()->create([
        'key' => 'theme.preset',
        'value' => 'rose',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'theme.neutral_palette',
        'value' => 'stone',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'mail.password',
        'value' => 'secret-password',
        'autoload' => false,
    ]);

    Setting::factory()->create([
        'key' => 'general.site_name',
        'value' => 'TNTT My Van',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'general.site_tagline',
        'value' => 'Nen tang quan ly thieu nhi giao xu',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'branding.favicon',
        'value' => '/storage/favicon.png',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'branding.login_image',
        'value' => '/storage/login.png',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'general.meta_keywords',
        'value' => 'tntt,myvan',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'general.meta_description',
        'value' => 'mo ta he thong',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'social.facebook_url',
        'value' => 'https://facebook.com/tnttmyvan',
        'autoload' => true,
    ]);

    $siteSettings = app(SiteSettings::class);

    expect($siteSettings->get('theme.preset'))->toBe('rose')
        ->and($siteSettings->get('theme.neutral_palette'))->toBe('stone')
        ->and($siteSettings->get('mail.password'))->toBeNull()
        ->and($siteSettings->general()->siteName())->toBe('TNTT My Van')
        ->and($siteSettings->general()->siteTagline())->toBe('Nen tang quan ly thieu nhi giao xu')
        ->and($siteSettings->branding()->favicon())->toBe('/storage/favicon.png')
        ->and($siteSettings->branding()->loginImage())->toBe('/storage/login.png')
        ->and($siteSettings->shared())->toMatchArray([
            'themePreset' => 'rose',
            'themeNeutralPalette' => 'stone',
            'siteTitle' => 'TNTT My Van',
            'siteTagline' => 'Nen tang quan ly thieu nhi giao xu',
            'siteMetaKeywords' => 'tntt,myvan',
            'siteMetaDescription' => 'mo ta he thong',
            'siteFavicon' => '/storage/favicon.png',
            'siteLoginImage' => '/storage/login.png',
            'siteFacebookUrl' => 'https://facebook.com/tnttmyvan',
        ]);
});

test('site settings cache is refreshed automatically when a setting changes', function () {
    $setting = Setting::factory()->create([
        'key' => 'theme.preset',
        'value' => 'sky',
        'autoload' => true,
    ]);

    $siteSettings = app(SiteSettings::class);

    expect($siteSettings->get('theme.preset'))->toBe('sky');

    $setting->update(['value' => 'rose']);

    expect($siteSettings->get('theme.preset'))->toBe('rose');
});

test('app service provider shares site settings with the dashboard layout', function () {
    Setting::factory()->create([
        'key' => 'theme.preset',
        'value' => 'rose',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'theme.neutral_palette',
        'value' => 'stone',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'general.site_name',
        'value' => 'Doan TNTT Gx My Van',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'general.site_tagline',
        'value' => 'Nen tang quan ly thieu nhi giao xu',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'general.meta_description',
        'value' => 'Mo ta tu settings',
        'autoload' => true,
    ]);

    Setting::factory()->create([
        'key' => 'branding.favicon',
        'value' => 'images/sites/favicon-test.png',
        'autoload' => true,
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee("themePreset: 'rose'", false)
        ->assertSee("themeNeutralPalette: 'stone'", false)
        ->assertSee('Doan TNTT Gx My Van', false)
        ->assertSee('content="Mo ta tu settings"', false)
        ->assertSee('images/sites/favicon-test.png', false);
});
