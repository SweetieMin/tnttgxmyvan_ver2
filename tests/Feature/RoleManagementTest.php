<?php

use App\Livewire\Admin\Access\Roles\RoleActions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'access.role.view',
        'access.role.create',
        'access.role.update',
        'access.role.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the roles page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('access.role.view');

    $response = $this->actingAs($user)->get(route('admin.access.roles'));

    $response->assertOk()
        ->assertSeeText(__('Select all'));
});

test('users without role view permission cannot visit the roles page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.access.roles'));

    $response->assertForbidden();
});

test('roles can be created and updated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'access.role.view',
        'access.role.create',
        'access.role.update',
    ]);

    Permission::findOrCreate('access.users.view', 'web');

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->set('roleName', 'Content Manager')
        ->set('selectedPermissions', ['access.users.view'])
        ->call('saveRole')
        ->assertHasNoErrors();

    $role = Role::findByName('Content Manager', 'web');

    expect($role->hasPermissionTo('access.users.view'))->toBeTrue();

    Livewire::test(RoleActions::class)
        ->call('openEditModal', $role->id)
        ->set('roleName', 'Content Editor')
        ->call('saveRole')
        ->assertHasNoErrors();

    expect(Role::findByName('Content Editor', 'web'))->not->toBeNull();
});

test('roles assigned to users cannot be deleted', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'access.role.view',
        'access.role.delete',
    ]);

    $role = Role::findOrCreate('Assigned Role', 'web');
    $user->assignRole($role);

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->call('confirmDeleteRole', $role->id)
        ->call('deleteRole')
        ->assertHasErrors(['deleteRole']);

    expect(Role::findByName('Assigned Role', 'web'))->not->toBeNull();
});
