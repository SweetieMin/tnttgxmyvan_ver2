<?php

use App\Foundation\SidebarNavigation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

beforeEach(function () {
    collect([
        'management.academic-year.view',
        'gradebook.enrollment.view',
        'attendance.gradebook.view',
        'arrangement.class-assignment.view',
        'arrangement.sector-assignment.view',
        'arrangement.attendance-schedule.view',
        'attendance.attendance-checkin.view',
        'attendance.activity-point.view',
        'review.promotion.view',
        'finance.transaction.view',
        'settings.log.activity.view',
        'personnel.user.view',
        'personnel.catechist.view',
        'personnel.child.view',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('sidebar navigation includes newly scaffolded academic workflow items when the user can access them', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'gradebook.enrollment.view',
        'attendance.gradebook.view',
        'arrangement.class-assignment.view',
        'arrangement.sector-assignment.view',
        'arrangement.attendance-schedule.view',
        'review.promotion.view',
        'attendance.attendance-checkin.view',
    ]);

    $navigation = app(SidebarNavigation::class)->for($user);

    $reviewSection = collect($navigation['primary'])
        ->firstWhere('label', __('Review'));

    expect($reviewSection)->not->toBeNull()
        ->and(collect($reviewSection['items'])->pluck('label')->all())->toBe([
            __('Promotions'),
        ]);

    $attendanceSection = collect($navigation['primary'])
        ->firstWhere('label', __('Attendance'));

    expect($attendanceSection)->not->toBeNull()
        ->and(collect($attendanceSection['items'])->pluck('label')->all())->toBe([
            __('Attendance check-ins'),
            __('Gradebooks'),
        ]);

    $arrangementSection = collect($navigation['primary'])
        ->firstWhere('label', __('Arrangement'));

    expect($arrangementSection)->not->toBeNull()
        ->and(collect($arrangementSection['items'])->pluck('label')->all())->toBe([
            __('Class assignments'),
            __('Sector assignments'),
            __('Attendance schedules'),
        ]);
});

test('sidebar navigation only includes sections and items the user can access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-year.view',
        'finance.transaction.view',
    ]);

    $navigation = app(SidebarNavigation::class)->for($user);

    expect($navigation['primary'])->toHaveCount(3)
        ->and($navigation['primary'][0]['label'])->toBe(__('General'))
        ->and(collect($navigation['primary'][1]['items'])->pluck('label')->all())->toBe([__('Academic years')])
        ->and(collect($navigation['primary'][2]['items'])->pluck('label')->all())->toBe([__('Common fund')])
        ->and($navigation['secondary'])->toBe([]);
});

test('sidebar navigation includes secondary advance items when the user has settings permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.log.activity.view');

    $navigation = app(SidebarNavigation::class)->for($user);

    expect($navigation['secondary'])->toHaveCount(1)
        ->and($navigation['secondary'][0]['label'])->toBe(__('Advance'))
        ->and(collect($navigation['secondary'][0]['items'])->pluck('label')->all())->toBe([__('System logs')]);
});

test('sidebar navigation includes only the personnel groups the user can access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('personnel.child.view');

    $navigation = app(SidebarNavigation::class)->for($user);
    $personnelSection = collect($navigation['primary'])
        ->firstWhere('label', __('Personnel'));

    expect($personnelSection)->not->toBeNull()
        ->and(collect($personnelSection['items'])->pluck('label')->all())->toBe([__('Children')]);
});

test('personnel edit routes only mark the current group as active in the sidebar', function () {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo([
        'personnel.user.view',
        'personnel.catechist.view',
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('Giáo Lý Viên', 'web'));

    $request = Request::create(route('admin.personnel.users.edit', [
        'group' => 'catechists',
        'user' => $user,
    ]));

    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);
    app()->instance('request', $request);

    $navigation = app(SidebarNavigation::class)->for($viewer);
    $personnelSection = collect($navigation['primary'])
        ->firstWhere('label', __('Personnel'));

    expect($personnelSection)->not->toBeNull();

    $items = collect($personnelSection['items'])->keyBy('label');

    expect($items[__('All users')]['active'])->toBeFalse()
        ->and($items[__('Catechists')]['active'])->toBeTrue();
});

test('authenticated admin pages include the sidebar scroll persistence hooks', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('personnel.user.view');

    $this->actingAs($user)
        ->get(route('admin.personnel.users'))
        ->assertOk()
        ->assertSee('app-sidebar-scroll-top', false)
        ->assertSee('data-app-sidebar-scroll', false)
        ->assertSee('livewire:navigating.window', false)
        ->assertSee('livewire:navigated.window', false);
});
