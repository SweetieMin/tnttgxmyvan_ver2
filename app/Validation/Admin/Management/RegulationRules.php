<?php

namespace App\Validation\Admin\Management;

use Illuminate\Validation\Rule;

class RegulationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:65535'],
            'type' => ['required', 'string', Rule::in(['plus', 'minus'])],
            'status' => ['required', 'string', Rule::in(['applied', 'not_applied', 'pending'])],
            'points' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'description.required' => __('Description is required.'),
            'type.required' => __('Type is required.'),
            'status.required' => __('Status is required.'),
            'points.required' => __('Points are required.'),
            'points.integer' => __('Points must be an integer.'),
        ];
    }
}
