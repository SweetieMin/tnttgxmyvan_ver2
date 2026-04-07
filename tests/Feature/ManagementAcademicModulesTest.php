<?php

use App\Models\Permission;
use App\Models\User;

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
