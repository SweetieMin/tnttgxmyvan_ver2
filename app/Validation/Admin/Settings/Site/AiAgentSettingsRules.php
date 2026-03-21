<?php

namespace App\Validation\Admin\Settings\Site;

class AiAgentSettingsRules
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(string $key): array
    {
        $rules = [
            'editingValue' => ['required', 'string', 'max:2048'],
        ];

        if (str_ends_with($key, '.base_url')) {
            $rules['editingValue'][] = 'url';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'editingValue.required' => __('The value field is required.'),
            'editingValue.string' => __('The value must be a valid string.'),
            'editingValue.max' => __('The value may not be greater than 2048 characters.'),
            'editingValue.url' => __('The value must be a valid URL.'),
        ];
    }
}
