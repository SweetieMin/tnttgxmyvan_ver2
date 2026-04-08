<?php

use App\Livewire\Admin\Review\Promotions\PromotionActions;
use App\Livewire\Admin\Review\Promotions\PromotionList;
use App\Models\AcademicCourse;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use App\Models\EnrollmentPromotionReview;
use App\Models\Permission;
use App\Models\Program;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'arrangement.enrollment.view',
        'attendance.gradebook.view',
        'arrangement.class-assignment.view',
        'arrangement.sector-assignment.view',
        'arrangement.attendance-schedule.view',
        'attendance.attendance-checkin.view',
        'attendance.activity-point.view',
        'review.promotion.view',
        'review.promotion.update',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

it('allows authorized users to visit the new academic management modules', function (string $permission, string $routeName, string $label) {
    $user = User::factory()->create();
    $user->givePermissionTo($permission);

    $response = $this->actingAs($user)->get(route($routeName));

    $response->assertOk()
        ->assertSeeText(__($label));
})->with([
    ['arrangement.enrollment.view', 'admin.arrangement.enrollments', 'Enrollments'],
    ['attendance.gradebook.view', 'admin.attendance.gradebooks', 'Gradebooks'],
    ['arrangement.class-assignment.view', 'admin.arrangement.class-assignments', 'Class assignments'],
    ['arrangement.sector-assignment.view', 'admin.arrangement.sector-assignments', 'Sector assignments'],
    ['arrangement.attendance-schedule.view', 'admin.arrangement.attendance-schedules', 'Attendance schedules'],
    ['attendance.attendance-checkin.view', 'admin.attendance.attendance-checkins', 'Attendance check-ins'],
    ['attendance.activity-point.view', 'admin.attendance.activity-points', 'Activity points'],
    ['review.promotion.view', 'admin.review.promotions', 'Promotions'],
]);

it('shows only children pending promotion review on the promotions page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('review.promotion.view');

    $academicYear = AcademicYear::factory()->create([
        'name' => 'NK24-25',
        'status_academic' => 'finished',
        'catechism_start_date' => '2024-09-01',
    ]);

    AcademicYear::factory()->create([
        'name' => 'NK25-26',
        'status_academic' => 'finished',
        'catechism_start_date' => '2025-09-01',
    ]);

    $program = Program::factory()->create([
        'ordering' => 1,
    ]);

    $course = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'ordering' => 1,
        'course_name' => 'Khai Tâm 1',
        'sector_name' => 'Tiền Ấu 1',
        'catechism_avg_score' => 5.00,
        'catechism_training_score' => 5.00,
        'activity_score' => 200,
    ]);

    $pendingChild = User::factory()->create([
        'last_name' => 'Nguyễn',
        'name' => 'An',
    ]);

    $passedChild = User::factory()->create([
        'last_name' => 'Trần',
        'name' => 'Bình',
    ]);

    AcademicEnrollment::factory()->create([
        'user_id' => $pendingChild->id,
        'academic_year_id' => $academicYear->id,
        'academic_course_id' => $course->id,
        'status' => 'pending_review',
        'review_status' => 'pending_review',
        'final_catechism_score' => 4.50,
        'final_conduct_score' => 4.00,
        'final_activity_score' => 150,
        'is_eligible_for_promotion' => false,
    ]);

    AcademicEnrollment::factory()->create([
        'user_id' => $passedChild->id,
        'academic_year_id' => $academicYear->id,
        'academic_course_id' => $course->id,
        'status' => 'passed',
        'review_status' => 'not_required',
        'final_catechism_score' => 7.00,
        'final_conduct_score' => 8.00,
        'final_activity_score' => 220,
        'is_eligible_for_promotion' => true,
    ]);

    $response = $this->actingAs($user)->get(route('admin.review.promotions'));

    $response->assertOk()
        ->assertSeeText(__('Pending promotion reviews'))
        ->assertSeeText('NK24-25')
        ->assertSeeText('Nguyễn An')
        ->assertSeeText('Khai Tâm 1')
        ->assertSeeText('4.50')
        ->assertSeeText('150')
        ->assertDontSeeText('Trần Bình');
});

it('allows authorized users to approve a pending promotion review with a note', function () {
    $user = User::factory()->create([
        'last_name' => 'Huỳnh',
        'name' => 'Trưởng',
    ]);
    $user->givePermissionTo([
        'review.promotion.view',
        'review.promotion.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'name' => 'NK24-25',
        'status_academic' => 'finished',
        'catechism_start_date' => '2024-09-01',
    ]);

    $program = Program::factory()->create([
        'ordering' => 1,
    ]);

    $course = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'ordering' => 1,
        'course_name' => 'Khai Tâm 1',
        'sector_name' => 'Tiền Ấu 1',
        'catechism_avg_score' => 5.00,
        'catechism_training_score' => 5.00,
        'activity_score' => 200,
    ]);

    $pendingChild = User::factory()->create([
        'last_name' => 'Nguyễn',
        'name' => 'An',
    ]);

    $pendingEnrollment = AcademicEnrollment::factory()->create([
        'user_id' => $pendingChild->id,
        'academic_year_id' => $academicYear->id,
        'academic_course_id' => $course->id,
        'status' => 'pending_review',
        'review_status' => 'pending_review',
        'final_catechism_score' => 4.50,
        'final_conduct_score' => 4.00,
        'final_activity_score' => 150,
        'is_eligible_for_promotion' => false,
    ]);

    $this->actingAs($user);

    Livewire::test(PromotionActions::class)
        ->call('openApprovalModal', $pendingEnrollment->id)
        ->assertSet('showApprovalModal', true)
        ->set('approvalNote', 'Đủ điều kiện lên lớp sau khi đã họp xét.')
        ->call('approvePromotion')
        ->assertSet('showApprovalModal', false);

    $pendingEnrollment = $pendingEnrollment->fresh();

    expect($pendingEnrollment)->not->toBeNull()
        ->and($pendingEnrollment?->status)->toBe('passed')
        ->and($pendingEnrollment?->review_status)->toBe('not_required')
        ->and($pendingEnrollment?->is_eligible_for_promotion)->toBeTrue()
        ->and($pendingEnrollment?->reviewed_by)->toBe($user->id)
        ->and($pendingEnrollment?->reviewed_at)->not->toBeNull()
        ->and($pendingEnrollment?->review_note)->toBe('Đủ điều kiện lên lớp sau khi đã họp xét.');

    $promotionReview = EnrollmentPromotionReview::query()
        ->where('academic_enrollment_id', $pendingEnrollment->id)
        ->first();

    expect($promotionReview)->not->toBeNull()
        ->and($promotionReview?->decision)->toBe('promoted')
        ->and($promotionReview?->reviewed_by)->toBe($user->id)
        ->and($promotionReview?->reviewed_at)->not->toBeNull()
        ->and($promotionReview?->note)->toBe('Đủ điều kiện lên lớp sau khi đã họp xét.');

    Livewire::test(PromotionList::class)
        ->set('tableFilters', [
            'academic_year_id' => [
                'value' => (string) $academicYear->id,
            ],
            'review_scope' => [
                'value' => 'reviewed',
            ],
        ])
        ->assertSeeText('Nguyễn An')
        ->assertSeeText('Huỳnh Trưởng')
        ->assertSeeText('Đủ điều kiện lên lớp sau khi đã họp xét.')
        ->assertSeeText(__('Promoted'));
});
