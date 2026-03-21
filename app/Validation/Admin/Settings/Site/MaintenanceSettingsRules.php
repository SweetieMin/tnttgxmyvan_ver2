<?php

namespace App\Validation\Admin\Settings\Site;

class MaintenanceSettingsRules
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'is_maintenance' => ['required', 'boolean'],
            'secret_key' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
            'start_at' => ['nullable', 'string', 'max:255'],
            'end_at' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [];
    }
}
