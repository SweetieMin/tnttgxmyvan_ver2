<?php

namespace App\Foundation\SiteSettings;

class BrandingSettings
{
    /**
     * @param  array<string, string>  $settings
     */
    public function __construct(
        protected array $settings,
    ) {}

    public function logo(): ?string
    {
        return $this->settings['branding.logo'] ?: null;
    }

    public function favicon(): ?string
    {
        return $this->settings['branding.favicon'] ?: null;
    }

    public function loginImage(): ?string
    {
        return $this->settings['branding.login_image'] ?: null;
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'logo' => $this->logo(),
            'favicon' => $this->favicon(),
            'login_image' => $this->loginImage(),
        ];
    }
}
