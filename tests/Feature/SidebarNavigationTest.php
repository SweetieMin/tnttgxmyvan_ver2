<?php

use App\Foundation\SidebarNavigation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

beforeEach(function () {
    collect([
        'management.academic-year.view',
        'finance.transaction.view',
        'settings.log.activity.view',
        'personnel.user.view',
        'personnel.catechist.view',
        'personnel.child.view',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
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
