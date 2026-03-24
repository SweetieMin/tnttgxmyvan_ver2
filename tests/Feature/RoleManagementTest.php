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
    $manageableRole = Role::findOrCreate('Catechist', 'web');
    $secondaryManageableRole = Role::findOrCreate('Assistant Catechist', 'web');

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->set('roleName', 'Content Manager')
        ->set('selectedPermissions', ['access.users.view'])
        ->set('selectedManageableRoles', [$manageableRole->id])
        ->call('saveRole')
        ->assertHasNoErrors();

    $role = Role::findByName('Content Manager', 'web');

    expect($role->hasPermissionTo('access.users.view'))->toBeTrue()
        ->and($role->manageableRoles()->pluck('roles.id')->all())->toBe([$manageableRole->id]);

    Livewire::test(RoleActions::class)
        ->call('openEditModal', $role->id)
        ->set('roleName', 'Content Editor')
        ->set('selectedManageableRoles', [$secondaryManageableRole->id])
        ->call('saveRole')
        ->assertHasNoErrors();

    $updatedRole = Role::findByName('Content Editor', 'web');

    expect($updatedRole)->not->toBeNull()
        ->and($updatedRole->manageableRoles()->pluck('roles.id')->all())->toBe([$secondaryManageableRole->id]);
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

test('role save button only appears after the form changes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'access.role.view',
        'access.role.create',
        'access.role.update',
    ]);

    $role = Role::findOrCreate('Existing Role', 'web');

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->call('openCreateModal')
        ->call('hasRoleChanges')
        ->assertReturned(false)
        ->set('roleName', 'New Role')
        ->call('hasRoleChanges')
        ->assertReturned(true)
        ->call('openEditModal', $role->id)
        ->call('hasRoleChanges')
        ->assertReturned(false)
        ->set('roleName', 'Updated Role')
        ->call('hasRoleChanges')
        ->assertReturned(true);
});

test('role managed roles list excludes admin and the current role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'access.role.view',
        'access.role.update',
    ]);

    Role::findOrCreate('Admin', 'web');
    $managerRole = Role::findOrCreate('Manager', 'web');
    Role::findOrCreate('Staff', 'web');

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->call('openEditModal', $managerRole->id)
        ->assertDontSeeText('Admin')
        ->assertDontSeeText('Manager')
        ->assertSeeText('Staff');
});

test('role list shows the managed roles count', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('access.role.view');

    $managerRole = Role::findOrCreate('Manager', 'web');
    $staffRole = Role::findOrCreate('Staff', 'web');
    $catechistRole = Role::findOrCreate('Catechist', 'web');

    $managerRole->manageableRoles()->sync([$staffRole->id, $catechistRole->id]);

    $response = $this->actingAs($user)->get(route('admin.access.roles'));

    $response->assertOk()
        ->assertSeeText(__('Managed roles'))
        ->assertSeeText('2');
});
