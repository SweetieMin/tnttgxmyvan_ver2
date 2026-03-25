<?php

namespace Database\Seeders;

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AcademicCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = AcademicYear::query()
            ->orderBy('catechism_start_date')
            ->get();

        $programs = Program::query()
            ->orderBy('ordering')
            ->orderBy('course')
            ->get();

        foreach ($academicYears as $academicYear) {
            foreach ($programs as $program) {
                AcademicCourse::query()->updateOrCreate([
                    'academic_year_id' => $academicYear->id,
                    'course_name' => $program->course,
                    'sector_name' => $program->sector,
                ], [
                    'program_id' => $program->id,
                    'ordering' => $program->ordering,
                    'catechism_avg_score' => $academicYear->catechism_avg_score,
                    'catechism_training_score' => $academicYear->catechism_training_score,
                    'activity_score' => $academicYear->activity_score,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
