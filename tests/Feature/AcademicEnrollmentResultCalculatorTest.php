<?php

use App\Models\AcademicCourse;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use App\Models\EnrollmentActivityPoint;
use App\Models\EnrollmentSemesterScore;
use App\Models\Program;
use App\Models\User;
use App\Support\AcademicEnrollmentResultCalculator;

test('it calculates yearly results and marks the enrollment as eligible when all conditions are met', function () {
    $academicYear = AcademicYear::factory()->create();
    $program = Program::factory()->create();
    $academicCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'catechism_avg_score' => 7.00,
        'catechism_training_score' => 7.00,
        'activity_score' => 120,
    ]);

    $academicEnrollment = AcademicEnrollment::factory()->create([
        'user_id' => User::factory()->create()->id,
        'academic_year_id' => $academicYear->id,
        'academic_course_id' => $academicCourse->id,
    ]);

    EnrollmentSemesterScore::factory()->create([
        'academic_enrollment_id' => $academicEnrollment->id,
        'semester' => 1,
        'month_score_1' => 7.00,
        'month_score_2' => 8.00,
        'month_score_3' => 8.00,
        'month_score_4' => 9.00,
        'exam_score' => 8.00,
        'conduct_score' => 8.00,
    ]);

    EnrollmentSemesterScore::factory()->create([
        'academic_enrollment_id' => $academicEnrollment->id,
        'semester' => 2,
        'month_score_1' => 8.00,
        'month_score_2' => 8.00,
        'month_score_3' => 9.00,
        'month_score_4' => 9.00,
        'exam_score' => 9.00,
        'conduct_score' => 8.00,
    ]);

    EnrollmentActivityPoint::factory()->create([
        'academic_enrollment_id' => $academicEnrollment->id,
        'points' => 80,
    ]);

    EnrollmentActivityPoint::factory()->create([
        'academic_enrollment_id' => $academicEnrollment->id,
        'points' => 45,
    ]);

    $result = app(AcademicEnrollmentResultCalculator::class)->calculate($academicEnrollment->fresh());

    expect($result['semester_1_score'])->toBe(8.0)
        ->and($result['semester_2_score'])->toBe(8.67)
        ->and($result['final_catechism_score'])->toBe(8.34)
        ->and($result['final_conduct_score'])->toBe(8.0)
        ->and($result['final_activity_score'])->toBe(125)
        ->and($result['is_eligible_for_promotion'])->toBeTrue()
        ->and($result['status'])->toBe('passed')
        ->and($result['review_status'])->toBe('not_required');
});

test('it flags the enrollment for review when one of the yearly conditions is not met', function () {
    $academicYear = AcademicYear::factory()->create();
    $program = Program::factory()->create();
    $academicCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'catechism_avg_score' => 7.00,
        'catechism_training_score' => 7.00,
        'activity_score' => 150,
    ]);

    $academicEnrollment = AcademicEnrollment::factory()->create([
        'user_id' => User::factory()->create()->id,
        'academic_year_id' => $academicYear->id,
        'academic_course_id' => $academicCourse->id,
    ]);

    EnrollmentSemesterScore::factory()->create([
        'academic_enrollment_id' => $academicEnrollment->id,
        'semester' => 1,
        'month_score_1' => 7.00,
        'month_score_2' => 7.00,
        'month_score_3' => 7.00,
        'month_score_4' => 7.00,
        'exam_score' => 7.00,
        'conduct_score' => 7.50,
    ]);

    EnrollmentSemesterScore::factory()->create([
        'academic_enrollment_id' => $academicEnrollment->id,
        'semester' => 2,
        'month_score_1' => 8.00,
        'month_score_2' => 8.00,
        'month_score_3' => 8.00,
        'month_score_4' => 8.00,
        'exam_score' => 8.00,
        'conduct_score' => 7.50,
    ]);

    EnrollmentActivityPoint::factory()->create([
        'academic_enrollment_id' => $academicEnrollment->id,
        'points' => 120,
    ]);

    $result = app(AcademicEnrollmentResultCalculator::class)->calculate($academicEnrollment->fresh());

    expect($result['final_catechism_score'])->toBe(7.5)
        ->and($result['final_conduct_score'])->toBe(7.5)
        ->and($result['final_activity_score'])->toBe(120)
        ->and($result['is_eligible_for_promotion'])->toBeFalse()
        ->and($result['status'])->toBe('pending_review')
        ->and($result['review_status'])->toBe('pending_review');
});

test('it returns incomplete results while semester scores are still missing', function () {
    $academicYear = AcademicYear::factory()->create();
    $program = Program::factory()->create();
    $academicCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
    ]);

    $academicEnrollment = AcademicEnrollment::factory()->create([
        'user_id' => User::factory()->create()->id,
        'academic_year_id' => $academicYear->id,
        'academic_course_id' => $academicCourse->id,
    ]);

    EnrollmentSemesterScore::factory()->create([
        'academic_enrollment_id' => $academicEnrollment->id,
        'semester' => 1,
        'month_score_1' => 7.00,
        'month_score_2' => 8.00,
        'month_score_3' => null,
        'month_score_4' => 9.00,
        'exam_score' => 8.00,
        'conduct_score' => 8.00,
    ]);

    $result = app(AcademicEnrollmentResultCalculator::class)->calculate($academicEnrollment->fresh());

    expect($result['semester_1_score'])->toBeNull()
        ->and($result['semester_2_score'])->toBeNull()
        ->and($result['final_catechism_score'])->toBeNull()
        ->and($result['final_conduct_score'])->toBeNull()
        ->and($result['is_eligible_for_promotion'])->toBeNull()
        ->and($result['status'])->toBe('studying')
        ->and($result['review_status'])->toBe('not_required');
});
