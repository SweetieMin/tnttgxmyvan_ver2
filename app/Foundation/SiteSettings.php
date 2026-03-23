<?php

namespace App\Foundation;

use App\Foundation\SiteSettings\BrandingSettings;
use App\Foundation\SiteSettings\GeneralSettings;
use App\Foundation\SiteSettings\SocialSettings;
use App\Foundation\SiteSettings\ThemeSettings;
use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSettings
{
    /**
     * @var array<string, string>
     */
    protected array $defaults = [
        'theme.preset' => 'sky',
        'theme.neutral_palette' => 'gray',
        'theme.seasonal_enabled' => '0',
        'branding.logo' => '',
        'branding.favicon' => '',
        'branding.login_image' => '',
        'general.site_name' => '',
        'general.site_tagline' => '',
        'general.site_email' => '',
        'general.site_phone' => '',
        'general.meta_keywords' => '',
        'general.meta_description' => '',
        'social.facebook_url' => '',
        'social.instagram_url' => '',
        'social.youtube_url' => '',
        'social.tiktok_url' => '',
    ];

    /**
     * @var array<string, string>
     */
    protected array $sharedKeys = [
        'themePreset' => 'theme.preset',
        'themeNeutralPalette' => 'theme.neutral_palette',
        'themeSeasonalEnabled' => 'theme.seasonal_enabled',
        'siteTitle' => 'general.site_name',
        'siteTagline' => 'general.site_tagline',
        'siteMetaKeywords' => 'general.meta_keywords',
        'siteMetaDescription' => 'general.meta_description',
        'siteLogo' => 'branding.logo',
        'siteFavicon' => 'branding.favicon',
        'siteLoginImage' => 'branding.login_image',
        'siteFacebookUrl' => 'social.facebook_url',
        'siteInstagramUrl' => 'social.instagram_url',
        'siteYoutubeUrl' => 'social.youtube_url',
        'siteTikTokUrl' => 'social.tiktok_url',
    ];

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        try {
            if (! Schema::hasTable('settings')) {
                return $this->defaults;
            }
        } catch (QueryException) {
            return $this->defaults;
        }

        /** @var array<string, string> $settings */
        $settings = Cache::rememberForever('site-settings.autoload', function (): array {
            /** @var array<string, string> $autoloadedSettings */
            $autoloadedSettings = Setting::query()
                ->where('autoload', true)
                ->pluck('value', 'key')
                ->map(fn (mixed $value): string => (string) $value)
                ->all();

            return array_replace($this->defaults, $autoloadedSettings);
        });

        return $settings;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default ?? $this->defaults[$key] ?? null;
    }

    public function theme(): ThemeSettings
    {
        return new ThemeSettings($this->all());
    }

    public function branding(): BrandingSettings
    {
        return new BrandingSettings($this->all());
    }

    public function general(): GeneralSettings
    {
        return new GeneralSettings($this->all());
    }

    public function social(): SocialSettings
    {
        return new SocialSettings($this->all());
    }

    /**
     * @return array<string, string>
     */
    public function shared(): array
    {
        return array_merge(
            [
                'themePreset' => $this->theme()->preset(),
                'themeNeutralPalette' => $this->theme()->neutralPalette(),
                'themeSeasonalEnabled' => $this->theme()->seasonalEnabled() ? '1' : '0',
                'siteTitle' => $this->general()->siteName(),
                'siteTagline' => $this->general()->siteTagline(),
                'siteMetaKeywords' => $this->general()->metaKeywords(),
                'siteMetaDescription' => $this->general()->metaDescription(),
                'siteLogo' => $this->branding()->logo(),
                'siteFavicon' => $this->branding()->favicon(),
                'siteLoginImage' => $this->branding()->loginImage(),
                'siteFacebookUrl' => $this->social()->facebookUrl(),
                'siteInstagramUrl' => $this->social()->instagramUrl(),
                'siteYoutubeUrl' => $this->social()->youtubeUrl(),
                'siteTikTokUrl' => $this->social()->tikTokUrl(),
            ],
            $this->sharedValues(),
        );
    }

    /**
     * @return array<string, string|null>
     */
    protected function sharedValues(): array
    {
        $sharedSettings = [];

        foreach ($this->sharedKeys as $sharedKey => $settingKey) {
            $sharedSettings[$sharedKey] = $this->get($settingKey);
        }

        return $sharedSettings;
    }

    public function forget(): void
    {
        Cache::forget('site-settings.autoload');
    }
}
