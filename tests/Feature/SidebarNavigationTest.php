<?php

use App\Foundation\SidebarNavigation;
use App\Models\Permission;
use App\Models\User;

beforeEach(function () {
    collect([
        'management.academic-year.view',
        'finance.transaction.view',
        'settings.log.activity.view',
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
