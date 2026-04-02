<?php

namespace Database\Factories;

use App\Models\AcademicEnrollment;
use App\Models\EnrollmentPromotionReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrollmentPromotionReview>
 */
class EnrollmentPromotionReviewFactory extends Factory
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
            'decision' => 'pending_review',
            'note' => fake()->optional()->sentence(),
        ];
    }
}
