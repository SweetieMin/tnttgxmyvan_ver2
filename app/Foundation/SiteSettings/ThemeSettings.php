<?php

namespace App\Foundation\SiteSettings;

class ThemeSettings
{
    /**
     * @param  array<string, string>  $settings
     */
    public function __construct(
        protected array $settings,
    ) {}

    public function preset(): string
    {
        return $this->settings['theme.preset'];
    }

    public function neutralPalette(): string
    {
        return $this->settings['theme.neutral_palette'];
    }

    public function seasonalEnabled(): bool
    {
        return $this->settings['theme.seasonal_enabled'] === '1';
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'preset' => $this->preset(),
            'neutral_palette' => $this->neutralPalette(),
            'seasonal_enabled' => $this->seasonalEnabled() ? '1' : '0',
        ];
    }
}
