<?php

use App\Models\AcademicCourse;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\AcademicCourseSeeder;
use Database\Seeders\AcademicEnrollmentSeeder;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\PersonnelRosterSeeder;
use Database\Seeders\ProgramSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;

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

test('academic enrollment seeder creates previous-year promotion data and current-year enrollments for children', function () {
    $this->seed(UserSeeder::class);
    $this->seed(RoleSeeder::class);
    $this->seed(PersonnelRosterSeeder::class);
    $this->seed(AcademicYearSeeder::class);
    $this->seed(ProgramSeeder::class);
    $this->seed(AcademicCourseSeeder::class);
    $this->seed(AcademicEnrollmentSeeder::class);

    $previousAcademicYear = AcademicYear::query()->where('name', 'NK24-25')->firstOrFail();
    $ongoingAcademicYear = AcademicYear::query()->where('name', 'NK25-26')->firstOrFail();

    $children = User::role('Thiếu Nhi')->get();

    $previousEnrollments = AcademicEnrollment::query()
        ->where('academic_year_id', $previousAcademicYear->id)
        ->get();

    $ongoingEnrollments = AcademicEnrollment::query()
        ->where('academic_year_id', $ongoingAcademicYear->id)
        ->get();

    expect($previousEnrollments)->toHaveCount($children->count())
        ->and($ongoingEnrollments)->toHaveCount($children->count())
        ->and($previousEnrollments->where('status', 'passed')->count())->toBeGreaterThan(0)
        ->and($previousEnrollments->where('status', 'pending_review')->count())->toBeGreaterThan(0)
        ->and($previousEnrollments->where('is_eligible_for_promotion', true)->count())->toBeGreaterThan(0)
        ->and($previousEnrollments->where('is_eligible_for_promotion', false)->count())->toBeGreaterThan(0)
        ->and($ongoingEnrollments->every(fn (AcademicEnrollment $enrollment): bool => $enrollment->status === 'studying'))->toBeTrue();
});
