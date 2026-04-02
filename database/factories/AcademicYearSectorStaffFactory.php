<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\AcademicYearSectorStaff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYearSectorStaff>
 */
class AcademicYearSectorStaffFactory extends Factory
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
            'sector_name' => fake()->randomElement(['Ấu', 'Thiếu', 'Nghĩa', 'Hiệp']),
            'user_id' => User::factory(),
            'assignment_type' => fake()->randomElement(['sector_leader', 'assistant_sector_leader', 'leader']),
        ];
    }
}
