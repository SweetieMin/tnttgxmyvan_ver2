<?php

namespace Database\Factories;

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicCourse>
 */
class AcademicCourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'program_id' => Program::factory(),
            'ordering' => fake()->numberBetween(1, 99),
            'course_name' => fake()->unique()->bothify('Lop ??'),
            'sector_name' => fake()->unique()->bothify('Nganh ??'),
            'is_active' => true,
        ];
    }
}
