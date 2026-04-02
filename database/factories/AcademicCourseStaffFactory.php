<?php

namespace Database\Factories;

use App\Models\AcademicCourse;
use App\Models\AcademicCourseStaff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicCourseStaff>
 */
class AcademicCourseStaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_course_id' => AcademicCourse::factory(),
            'user_id' => User::factory(),
            'assignment_type' => fake()->randomElement(['catechist', 'assistant_catechist']),
            'is_primary' => false,
        ];
    }
}
