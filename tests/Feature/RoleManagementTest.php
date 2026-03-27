<?php

use App\Livewire\Admin\Access\Roles\RoleActions;
use App\Models\Permission;
use App\Models\PersonnelRoleGroup;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

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

    Permission::findOrCreate('personnel.director.view', 'web');
    $manageableRole = Role::findOrCreate('Catechist', 'web');
    $secondaryManageableRole = Role::findOrCreate('Assistant Catechist', 'web');

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->set('roleName', 'Content Manager')
        ->set('selectedPermissions', ['personnel.director.view'])
        ->set('selectedManageableRoles', [$manageableRole->id])
        ->set('selectedPersonnelGroups', ['catechists'])
        ->call('saveRole')
        ->assertHasNoErrors();

    $role = Role::findByName('Content Manager', 'web');

    expect($role->hasPermissionTo('personnel.director.view'))->toBeTrue()
        ->and($role->manageableRoles()->pluck('roles.id')->all())->toBe([$manageableRole->id])
        ->and($role->personnelRoleGroups()->pluck('group_key')->all())->toBe(['catechists']);

    Livewire::test(RoleActions::class)
        ->call('openEditModal', $role->id)
        ->set('roleName', 'Content Editor')
        ->set('selectedManageableRoles', [$secondaryManageableRole->id])
        ->set('selectedPersonnelGroups', ['leaders', 'children'])
        ->call('saveRole')
        ->assertHasNoErrors();

    $updatedRole = Role::findByName('Content Editor', 'web');

    expect($updatedRole)->not->toBeNull()
        ->and($updatedRole->manageableRoles()->pluck('roles.id')->all())->toBe([$secondaryManageableRole->id])
        ->and($updatedRole->personnelRoleGroups()->pluck('group_key')->sort()->values()->all())->toBe(['children', 'leaders']);
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

test('manageable role activity logs store role names instead of ids', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'access.role.view',
        'access.role.create',
        'access.role.update',
    ]);

    $staffRole = Role::findOrCreate('Staff', 'web');
    $catechistRole = Role::findOrCreate('Catechist', 'web');

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->set('roleName', 'Coordinator')
        ->set('selectedManageableRoles', [$staffRole->id, $catechistRole->id])
        ->call('saveRole')
        ->assertHasNoErrors();

    $role = Role::findByName('Coordinator', 'web');

    /** @var Activity $activity */
    $activity = Activity::query()
        ->where('log_name', 'roles')
        ->where('subject_type', Role::class)
        ->where('subject_id', $role->id)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    /** @var array<string, mixed> $attributes */
    $attributes = $activity->properties->get('attributes', []);

    expect($attributes['attached_manageable_roles'] ?? null)
        ->toBe(['Staff', 'Catechist'])
        ->and($attributes['attached_manageable_role_ids'] ?? null)
        ->toBeNull();
});

test('role personnel group assignment is saved and logged with labels', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'access.role.view',
        'access.role.create',
        'access.role.update',
    ]);

    $this->actingAs($user);

    Livewire::test(RoleActions::class)
        ->set('roleName', 'Youth Coordinator')
        ->set('selectedPersonnelGroups', ['leaders', 'children'])
        ->call('saveRole')
        ->assertHasNoErrors();

    $role = Role::findByName('Youth Coordinator', 'web');

    expect($role->personnelRoleGroups()->pluck('group_key')->sort()->values()->all())
        ->toBe(['children', 'leaders']);

    /** @var Activity $activity */
    $activity = Activity::query()
        ->where('log_name', 'roles')
        ->where('subject_type', Role::class)
        ->where('subject_id', $role->id)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    /** @var array<string, mixed> $attributes */
    $attributes = $activity->properties->get('attributes', []);

    expect($attributes['attached_personnel_groups'] ?? null)
        ->toBe([__('Children'), __('Leaders')])
        ->and($attributes['detached_personnel_groups'] ?? null)
        ->toBe([]);

    $role->personnelRoleGroups()->delete();

    expect(PersonnelRoleGroup::query()->where('role_id', $role->id)->exists())->toBeFalse();
});
