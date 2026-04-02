<?php

namespace Database\Factories;

use App\Models\AcademicEnrollment;
use App\Models\EnrollmentActivityPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrollmentActivityPoint>
 */
class EnrollmentActivityPointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_enrollment_id' => AcademicEnrollment::factory(),
            'source_type' => 'manual_adjustment',
            'points' => fake()->numberBetween(-20, 20),
            'happened_at' => now(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
