<?php

namespace App\Foundation\SiteSettings;

class GeneralSettings
{
    /**
     * @param  array<string, string>  $settings
     */
    public function __construct(
        protected array $settings,
    ) {}

    public function siteName(): string
    {
        return $this->settings['general.site_name'];
    }

    public function siteTagline(): string
    {
        return $this->settings['general.site_tagline'];
    }

    public function siteEmail(): string
    {
        return $this->settings['general.site_email'];
    }

    public function sitePhone(): string
    {
        return $this->settings['general.site_phone'];
    }

    public function metaKeywords(): string
    {
        return $this->settings['general.meta_keywords'];
    }

    public function metaDescription(): string
    {
        return $this->settings['general.meta_description'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'site_name' => $this->siteName(),
            'site_tagline' => $this->siteTagline(),
            'site_email' => $this->siteEmail(),
            'site_phone' => $this->sitePhone(),
            'meta_keywords' => $this->metaKeywords(),
            'meta_description' => $this->metaDescription(),
        ];
    }
}
