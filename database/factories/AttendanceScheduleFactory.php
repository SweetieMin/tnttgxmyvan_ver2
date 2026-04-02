<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\AttendanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSchedule>
 */
class AttendanceScheduleFactory extends Factory
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
            'title' => fake()->sentence(3),
            'sector_name' => fake()->randomElement(['Ấu', 'Thiếu', 'Nghĩa', 'Hiệp']),
            'attendance_date' => fake()->date(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'points' => fake()->numberBetween(5, 20),
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
