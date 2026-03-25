<?php

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Program;
use Database\Seeders\AcademicCourseSeeder;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\ProgramSeeder;

test('academic course seeder creates course-sector classes for every academic year and program', function () {
    $this->seed(AcademicYearSeeder::class);
    $this->seed(ProgramSeeder::class);
    $this->seed(AcademicCourseSeeder::class);

    $academicYears = AcademicYear::query()->orderBy('catechism_start_date')->get();
    $programs = Program::query()->orderBy('ordering')->get();
    $academicCourses = AcademicCourse::query()
        ->orderBy('academic_year_id')
        ->orderBy('ordering')
        ->get();

    expect($academicCourses)->toHaveCount($academicYears->count() * $programs->count());

    foreach ($academicYears as $academicYear) {
        $coursesForYear = $academicCourses->where('academic_year_id', $academicYear->id)->values();

        expect($coursesForYear)->toHaveCount($programs->count());

        foreach ($programs as $index => $program) {
            $academicCourse = $coursesForYear[$index];

            expect($academicCourse->program_id)->toBe($program->id)
                ->and($academicCourse->ordering)->toBe($program->ordering)
                ->and($academicCourse->course_name)->toBe($program->course)
                ->and($academicCourse->sector_name)->toBe($program->sector)
                ->and((float) $academicCourse->catechism_avg_score)->toBe((float) $academicYear->catechism_avg_score)
                ->and((float) $academicCourse->catechism_training_score)->toBe((float) $academicYear->catechism_training_score)
                ->and($academicCourse->activity_score)->toBe($academicYear->activity_score)
                ->and($academicCourse->is_active)->toBeTrue();
        }
    }
});
