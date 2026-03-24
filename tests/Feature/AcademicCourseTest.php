<?php

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Program;
use Illuminate\Support\Facades\Schema;

test('academic courses table includes the expected columns', function () {
    expect(Schema::hasColumns('academic_courses', [
        'id',
        'academic_year_id',
        'program_id',
        'ordering',
        'course_name',
        'sector_name',
        'catechism_avg_score',
        'catechism_training_score',
        'activity_score',
        'is_active',
        'deleted_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('academic course belongs to an academic year and a program', function () {
    $academicCourse = AcademicCourse::factory()->create([
        'academic_year_id' => AcademicYear::factory(),
        'program_id' => Program::factory(),
        'course_name' => 'Them Suc 2',
        'sector_name' => 'Thieu 2',
        'catechism_avg_score' => 6.50,
        'catechism_training_score' => 7.00,
        'activity_score' => 180,
    ]);

    expect($academicCourse->academicYear)->toBeInstanceOf(AcademicYear::class)
        ->and($academicCourse->program)->toBeInstanceOf(Program::class)
        ->and($academicCourse->course_name)->toBe('Them Suc 2')
        ->and($academicCourse->sector_name)->toBe('Thieu 2')
        ->and($academicCourse->activity_score)->toBe(180);
});
