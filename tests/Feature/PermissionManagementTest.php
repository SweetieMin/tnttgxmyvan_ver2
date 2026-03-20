<?php

use App\Livewire\Admin\Access\Permissions\PermissionActions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'access.permission.view',
        'access.permission.create',
        'access.permission.update',
        'access.permission.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the permissions page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('access.permission.view');

    $response = $this->actingAs($user)->get(route('admin.access.permissions'));

    $response->assertOk();
});

test('users without permission view permission cannot visit the permissions page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.access.permissions'));

    $response->assertForbidden();
});

test('permissions can be created and updated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'access.permission.view',
        'access.permission.create',
        'access.permission.update',
    ]);

    $this->actingAs($user);

    Livewire::test(PermissionActions::class)
        ->set('permissionName', 'access.post.view')
        ->call('savePermission')
        ->assertHasNoErrors();

    $permission = Permission::findByName('access.post.view', 'web');

    Livewire::test(PermissionActions::class)
        ->call('openEditModal', $permission->id)
        ->set('permissionName', 'access.post.manage')
        ->call('savePermission')
        ->assertHasNoErrors();

    expect(Permission::findByName('access.post.manage', 'web'))->not->toBeNull();
});

test('permissions in use cannot be deleted', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'access.permission.view',
        'access.permission.delete',
    ]);

    $permission = Permission::findOrCreate('access.locked.view', 'web');
    $role = Role::findOrCreate('Permission Holder', 'web');
    $role->givePermissionTo($permission);

    $this->actingAs($user);

    Livewire::test(PermissionActions::class)
        ->call('confirmDeletePermission', $permission->id)
        ->call('deletePermission')
        ->assertHasErrors(['deletePermission']);

    expect(Permission::findByName('access.locked.view', 'web'))->not->toBeNull();
});
