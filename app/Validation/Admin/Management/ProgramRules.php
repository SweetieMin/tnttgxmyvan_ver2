<?php

namespace App\Validation\Admin\Management;

use App\Models\Program;
use Illuminate\Validation\Rule;

class ProgramRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $programId = null): array
    {
        return [
            'course' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Program::class, 'course')->ignore($programId),
            ],
            'sector' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Program::class, 'sector')->ignore($programId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'course.required' => __('Catechism class is required.'),
            'course.unique' => __('This catechism class already exists.'),
            'sector.required' => __('Sector is required.'),
            'sector.unique' => __('This sector already exists.'),
        ];
    }
}
