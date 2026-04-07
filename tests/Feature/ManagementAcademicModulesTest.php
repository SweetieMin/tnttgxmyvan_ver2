<?php

use App\Models\Permission;
use App\Models\User;

beforeEach(function () {
    collect([
        'management.enrollment.view',
        'management.gradebook.view',
        'arrangement.class-assignment.view',
        'arrangement.sector-assignment.view',
        'management.attendance-schedule.view',
        'management.attendance-checkin.view',
        'management.activity-point.view',
        'management.promotion.view',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

it('allows authorized users to visit the new academic management modules', function (string $permission, string $routeName, string $label) {
    $user = User::factory()->create();
    $user->givePermissionTo($permission);

    $response = $this->actingAs($user)->get(route($routeName));

    $response->assertOk()
        ->assertSeeText(__($label));
})->with([
    ['management.enrollment.view', 'admin.management.enrollments', 'Enrollments'],
    ['management.gradebook.view', 'admin.management.gradebooks', 'Gradebooks'],
    ['arrangement.class-assignment.view', 'admin.arrangement.class-assignments', 'Class assignments'],
    ['arrangement.sector-assignment.view', 'admin.arrangement.sector-assignments', 'Sector assignments'],
    ['management.attendance-schedule.view', 'admin.management.attendance-schedules', 'Attendance schedules'],
    ['management.attendance-checkin.view', 'admin.management.attendance-checkins', 'Attendance check-ins'],
    ['management.activity-point.view', 'admin.management.activity-points', 'Activity points'],
    ['management.promotion.view', 'admin.management.promotions', 'Promotions'],
]);
