<?php

namespace App\Validation\Admin\Management;

use App\Models\AcademicYear;
use Illuminate\Validation\Rule;

class AcademicYearRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $academicYearId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(AcademicYear::class, 'name')->ignore($academicYearId),
            ],
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'end_year' => ['required', 'integer', 'gt:start_year', 'min:2001', 'max:2101'],
            'catechism_start_date' => ['nullable', 'date'],
            'catechism_end_date' => ['nullable', 'date', 'after_or_equal:catechism_start_date'],
            'catechism_avg_score' => ['required', 'numeric', 'min:0'],
            'catechism_training_score' => ['required', 'numeric', 'min:0'],
            'activity_start_date' => ['nullable', 'date'],
            'activity_end_date' => ['nullable', 'date', 'after_or_equal:activity_start_date'],
            'activity_score' => ['required', 'integer', 'min:0'],
            'status_academic' => ['required', 'string', Rule::in(['upcoming', 'ongoing', 'finished'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'name.unique' => __('This academic year already exists.'),
            'start_year.required' => __('Start year is required.'),
            'end_year.required' => __('End year is required.'),
            'end_year.gt' => __('The end year must be greater than the start year.'),
            'catechism_end_date.after_or_equal' => __('The catechism end date must be after or equal to the start date.'),
            'activity_end_date.after_or_equal' => __('The activity end date must be after or equal to the start date.'),
        ];
    }
}
