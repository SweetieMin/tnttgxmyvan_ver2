<?php

namespace Database\Factories;

use App\Models\AcademicEnrollment;
use App\Models\EnrollmentSemesterScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrollmentSemesterScore>
 */
class EnrollmentSemesterScoreFactory extends Factory
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
            'semester' => 1,
            'month_score_1' => 7.00,
            'month_score_2' => 7.50,
            'month_score_3' => 8.00,
            'month_score_4' => 8.50,
            'exam_score' => 8.00,
            'conduct_score' => 8.00,
            'is_locked' => false,
        ];
    }
}
