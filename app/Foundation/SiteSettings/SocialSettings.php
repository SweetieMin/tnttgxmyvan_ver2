<?php

namespace App\Foundation\SiteSettings;

class SocialSettings
{
    /**
     * @param  array<string, string>  $settings
     */
    public function __construct(
        protected array $settings,
    ) {}

    public function facebookUrl(): string
    {
        return $this->settings['social.facebook_url'];
    }

    public function instagramUrl(): string
    {
        return $this->settings['social.instagram_url'];
    }

    public function youtubeUrl(): string
    {
        return $this->settings['social.youtube_url'];
    }

    public function tikTokUrl(): string
    {
        return $this->settings['social.tiktok_url'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'facebook_url' => $this->facebookUrl(),
            'instagram_url' => $this->instagramUrl(),
            'youtube_url' => $this->youtubeUrl(),
            'tiktok_url' => $this->tikTokUrl(),
        ];
    }
}
