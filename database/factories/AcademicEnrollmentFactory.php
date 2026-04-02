<?php

namespace Database\Factories;

use App\Models\AcademicCourse;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicEnrollment>
 */
class AcademicEnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'academic_course_id' => AcademicCourse::factory(),
            'status' => 'studying',
            'review_status' => 'not_required',
        ];
    }
}
