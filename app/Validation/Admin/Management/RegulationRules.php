<?php

namespace App\Validation\Admin\Management;

use Illuminate\Validation\Rule;

class RegulationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(string $pointsField = 'points'): array
    {
        return [
            'description' => ['required', 'string', 'max:65535'],
            'short_desc' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['plus', 'minus'])],
            'status' => ['required', 'string', Rule::in(['applied', 'not_applied', 'pending'])],
            $pointsField => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(string $pointsField = 'points'): array
    {
        return [
            'description.required' => __('Description is required.'),
            'short_desc.required' => __('Short description is required.'),
            'type.required' => __('Type is required.'),
            'status.required' => __('Status is required.'),
            "{$pointsField}.required" => __('Points are required.'),
            "{$pointsField}.integer" => __('Points must be an integer.'),
        ];
    }
}
