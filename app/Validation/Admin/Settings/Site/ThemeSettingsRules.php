<?php

namespace App\Validation\Admin\Settings\Site;

class ThemeSettingsRules
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'preset' => ['required', 'string'],
            'neutral_palette' => ['required', 'string'],
            'seasonal_enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'preset.required' => __('Theme preset is required.'),
            'neutral_palette.required' => __('Neutral palette is required.'),
        ];
    }
}
