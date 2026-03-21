<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = now()->year;
        $endYear = $startYear + 1;

        return [
            'name' => sprintf('NK%s-%s', substr((string) $startYear, -2), substr((string) $endYear, -2)),
            'catechism_start_date' => sprintf('%s-09-01', $startYear),
            'catechism_end_date' => sprintf('%s-07-31', $endYear),
            'catechism_avg_score' => 5.00,
            'catechism_training_score' => 5.00,
            'activity_start_date' => sprintf('%s-09-01', $startYear),
            'activity_end_date' => sprintf('%s-07-31', $endYear),
            'activity_score' => 150,
            'status_academic' => 'upcoming',
        ];
    }
}
