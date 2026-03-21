<?php

use App\Livewire\Admin\Access\Permissions\PermissionActions;
use App\Livewire\Admin\Access\Roles\RoleActions;
use App\Models\ActivityFailedLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'access.role.view',
        'access.role.create',
        'access.role.delete',
        'access.permission.view',
        'access.permission.create',
        'access.permission.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('role validation failures are not recorded in activity failed logs', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['access.role.view', 'access.role.create']);

    Role::findOrCreate('Existing Role', 'web');

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->set('roleName', 'Existing Role')
        ->call('saveRole')
        ->assertHasErrors(['roleName']);

    expect(ActivityFailedLog::query()
        ->where('log_name', 'roles')
        ->where('action', 'create')
        ->latest('id')
        ->doesntExist())->toBeTrue();
});

test('permission delete guard failures are not recorded in activity failed logs', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['access.permission.view', 'access.permission.create']);

    $permission = Permission::findOrCreate('access.failed-log.view', 'web');
    $role = Role::findOrCreate('Failed Log Holder', 'web');
    $role->givePermissionTo($permission);

    $user->givePermissionTo('access.permission.delete');

    $this->actingAs($user);

    Livewire::test(PermissionActions::class)
        ->call('confirmDeletePermission', $permission->id)
        ->call('deletePermission')
        ->assertHasErrors(['deletePermission']);

    expect(ActivityFailedLog::query()
        ->where('log_name', 'permissions')
        ->where('action', 'delete')
        ->latest('id')
        ->doesntExist())->toBeTrue();
});
