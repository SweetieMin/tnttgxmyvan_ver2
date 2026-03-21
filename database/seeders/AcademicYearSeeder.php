<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = [
            [
                'name' => 'NK24-25',
                'catechism_start_date' => '2024-09-01',
                'catechism_end_date' => '2025-06-30',
                'catechism_avg_score' => 5,
                'catechism_training_score' => 5,
                'activity_start_date' => '2024-09-01',
                'activity_end_date' => '2025-07-31',
                'activity_score' => 200,
                'status_academic' => 'finished',
            ],
            [
                'name' => 'NK25-26',
                'catechism_start_date' => '2025-09-01',
                'catechism_end_date' => '2026-06-30',
                'catechism_avg_score' => 5,
                'catechism_training_score' => 5,
                'activity_start_date' => '2025-09-01',
                'activity_end_date' => '2026-07-31',
                'activity_score' => 200,
                'status_academic' => 'ongoing',
            ],
            [
                'name' => 'NK26-27',
                'catechism_start_date' => '2026-09-01',
                'catechism_end_date' => '2027-06-30',
                'catechism_avg_score' => 5,
                'catechism_training_score' => 5,
                'activity_start_date' => '2026-09-01',
                'activity_end_date' => '2027-07-31',
                'activity_score' => 200,
                'status_academic' => 'upcoming',
            ],
            [
                'name' => 'NK27-28',
                'catechism_start_date' => '2027-09-01',
                'catechism_end_date' => '2028-06-30',
                'catechism_avg_score' => 5,
                'catechism_training_score' => 5,
                'activity_start_date' => '2027-09-01',
                'activity_end_date' => '2028-07-31',
                'activity_score' => 200,
                'status_academic' => 'upcoming',
            ],
            [
                'name' => 'NK28-29',
                'catechism_start_date' => '2028-09-01',
                'catechism_end_date' => '2029-06-30',
                'catechism_avg_score' => 5,
                'catechism_training_score' => 5,
                'activity_start_date' => '2028-09-01',
                'activity_end_date' => '2029-07-31',
                'activity_score' => 200,
                'status_academic' => 'upcoming',
            ],
        ];

        foreach ($years as $year) {
            AcademicYear::create($year);
        }
    }
}
