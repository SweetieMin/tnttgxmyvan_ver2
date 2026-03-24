<?php

namespace App\Validation\Admin\Management;

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Program;
use Illuminate\Validation\Rule;

class AcademicCourseRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $academicCourseId = null, ?int $academicYearId = null): array
    {
        return [
            'academic_year_id' => ['required', Rule::exists(AcademicYear::class, 'id')],
            'program_id' => ['required', Rule::exists(Program::class, 'id')],
            'ordering' => ['required', 'integer', 'min:1'],
            'course_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(AcademicCourse::class, 'course_name')
                    ->ignore($academicCourseId)
                    ->where(fn ($query) => $query->where('academic_year_id', $academicYearId)),
            ],
            'sector_name' => ['required', 'string', 'max:255'],
            'catechism_avg_score' => ['required', 'numeric', 'min:0'],
            'catechism_training_score' => ['required', 'numeric', 'min:0'],
            'activity_score' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'academic_year_id.required' => __('Academic year is required.'),
            'program_id.required' => __('Program is required.'),
            'ordering.required' => __('Ordering is required.'),
            'course_name.required' => __('Catechism class name is required.'),
            'course_name.unique' => __('This catechism class already exists in the selected academic year.'),
            'sector_name.required' => __('Sector name is required.'),
        ];
    }
}
