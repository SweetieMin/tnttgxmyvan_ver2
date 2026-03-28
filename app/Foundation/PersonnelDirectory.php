<?php

namespace App\Foundation;

use App\Models\PersonnelRoleGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PersonnelDirectory
{
    /**
     * @var array<string, array<int, string>>|null
     */
    protected ?array $roleNamesByGroup = null;

    protected ?bool $rolesHaveOrderingColumn = null;

    /**
     * @return array<string, array{
     *     label: string,
     *     icon: string,
     *     route: string,
     *     subtitle: string,
     *     permission: string,
     *     create_permission: string|null,
     *     update_permission: string|null,
     *     delete_permission: string|null
     * }>
     */
    public function pages(): array
    {
        return [
            'users' => [
                'label' => __('All users'),
                'icon' => 'user-group',
                'route' => 'admin.personnel.users',
                'subtitle' => __('Manage the full personnel directory in one place.'),
                'permission' => 'personnel.user.view',
                'create_permission' => 'personnel.user.create',
                'update_permission' => 'personnel.user.update',
                'delete_permission' => 'personnel.user.delete',
            ],
            'directors' => [
                'label' => __('Directors'),
                'icon' => 'sparkles',
                'route' => 'admin.personnel.directors',
                'subtitle' => __('Manage chaplains and deacons in the parish unit.'),
                'permission' => 'personnel.director.view',
                'create_permission' => 'personnel.director.create',
                'update_permission' => 'personnel.director.update',
                'delete_permission' => 'personnel.director.delete',
            ],
            'catechists' => [
                'label' => __('Catechists'),
                'icon' => 'book-open',
                'route' => 'admin.personnel.catechists',
                'subtitle' => __('Manage catechism teachers and their leadership roles.'),
                'permission' => 'personnel.catechist.view',
                'create_permission' => 'personnel.catechist.create',
                'update_permission' => 'personnel.catechist.update',
                'delete_permission' => 'personnel.catechist.delete',
            ],
            'leaders' => [
                'label' => __('Leaders'),
                'icon' => 'flag',
                'route' => 'admin.personnel.leaders',
                'subtitle' => __('Manage leaders, unit officers, and youth movement roles.'),
                'permission' => 'personnel.leader.view',
                'create_permission' => 'personnel.leader.create',
                'update_permission' => 'personnel.leader.update',
                'delete_permission' => 'personnel.leader.delete',
            ],
            'children' => [
                'label' => __('Children'),
                'icon' => 'users',
                'route' => 'admin.personnel.children',
                'subtitle' => __('Manage children who are studying or have completed the program.'),
                'permission' => 'personnel.child.view',
                'create_permission' => 'personnel.child.create',
                'update_permission' => 'personnel.child.update',
                'delete_permission' => 'personnel.child.delete',
            ],
            'deleted-users' => [
                'label' => __('Deleted users'),
                'icon' => 'archive-box-x-mark',
                'route' => 'admin.personnel.deleted-users',
                'subtitle' => __('Review personnel profiles that were moved out of the active directory.'),
                'permission' => 'personnel.deleted.view',
                'create_permission' => null,
                'update_permission' => null,
                'delete_permission' => null,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     icon: string,
     *     route: string,
     *     subtitle: string,
     *     permission: string,
     *     create_permission: string|null,
     *     update_permission: string|null,
     *     delete_permission: string|null
     * }>
     */
    public function groups(): array
    {
        return collect($this->pages())
            ->only([
                'directors',
                'catechists',
                'leaders',
                'children',
            ])
            ->all();
    }

    public function hasGroup(string $group): bool
    {
        return array_key_exists($group, $this->pages());
    }

    /**
     * @return array{
     *     label: string,
     *     icon: string,
     *     route: string,
     *     subtitle: string,
     *     permission: string,
     *     create_permission: string|null,
     *     update_permission: string|null,
     *     delete_permission: string|null
     * }
     */
    public function group(string $group): array
    {
        return $this->pages()[$group];
    }

    public function isGroupPage(string $group): bool
    {
        return array_key_exists($group, $this->groups());
    }

    public function isAllUsersPage(string $group): bool
    {
        return $group === 'users';
    }

    public function isDeletedUsersPage(string $group): bool
    {
        return $group === 'deleted-users';
    }

    /**
     * @return array<int, string>
     */
    public function roleNamesForGroup(string $group): array
    {
        return $this->mappedRoleNamesByGroup()[$group] ?? [];
    }

    public function permissionForGroup(string $group): string
    {
        return $this->group($group)['permission'];
    }

    public function createPermissionForGroup(string $group): ?string
    {
        return $this->group($group)['create_permission'];
    }

    public function updatePermissionForGroup(string $group): ?string
    {
        return $this->group($group)['update_permission'];
    }

    public function deletePermissionForGroup(string $group): ?string
    {
        return $this->group($group)['delete_permission'];
    }

    /**
     * @return array<int, string>
     */
    public function permissions(): array
    {
        return collect($this->pages())
            ->flatMap(fn (array $group): array => array_filter([
                $group['permission'],
                $group['create_permission'],
                $group['update_permission'],
                $group['delete_permission'],
            ]))
            ->unique()
            ->values()
            ->all();
    }

    public function detectGroupFromRoles(array $roleNames): ?string
    {
        return $this->groupsForRoles($roleNames)[0] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function groupsForRoles(array $roleNames): array
    {
        return array_values(array_filter(
            array_keys($this->groups()),
            fn (string $groupKey): bool => array_intersect($this->roleNamesForGroup($groupKey), $roleNames) !== [],
        ));
    }

    public function roleNamesBelongToGroup(array $roleNames, string $group): bool
    {
        if (! $this->isGroupPage($group)) {
            return false;
        }

        return array_intersect($this->roleNamesForGroup($group), $roleNames) !== [];
    }

    /**
     * @return array<int, string>
     */
    public function allPersonnelRoleNames(): array
    {
        return collect($this->mappedRoleNamesByGroup())
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function assignableGroups(): array
    {
        return collect($this->groups())
            ->map(fn (array $group, string $groupKey): array => [
                'value' => $groupKey,
                'label' => $group['label'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function manageableRoleNamesFor(?User $user, ?string $group = null): array
    {
        if ($user === null) {
            return [];
        }

        $personnelRoleNames = collect($this->allPersonnelRoleNames());

        if ($user->hasRole('Admin')) {
            return $this->orderedRoleNames($this->filterRoleNamesByGroup(
                $personnelRoleNames->all(),
                $group,
            ));
        }

        /** @var Collection<int, string> $manageableRoleNames */
        $manageableRoleNames = $user->roles()
            ->with('manageableRoles:id,name')
            ->get()
            ->flatMap(fn ($role): Collection => $role->manageableRoles->pluck('name'))
            ->intersect($personnelRoleNames)
            ->unique()
            ->values();

        return $this->orderedRoleNames($this->filterRoleNamesByGroup($manageableRoleNames->all(), $group));
    }

    /**
     * @param  array<int, string>  $roleNames
     * @return array<int, string>
     */
    protected function filterRoleNamesByGroup(array $roleNames, ?string $group): array
    {
        if ($group !== null && $this->isGroupPage($group)) {
            return array_values(array_intersect(
                $roleNames,
                $this->roleNamesForGroup($group),
            ));
        }

        return array_values($roleNames);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function mappedRoleNamesByGroup(): array
    {
        if ($this->roleNamesByGroup !== null) {
            return $this->roleNamesByGroup;
        }

        $emptyGroups = collect(array_keys($this->groups()))
            ->mapWithKeys(fn (string $groupKey): array => [$groupKey => []])
            ->all();

        $mappedGroups = PersonnelRoleGroup::query()
            ->select('personnel_role_groups.group_key', 'roles.name')
            ->join('roles', 'roles.id', '=', 'personnel_role_groups.role_id')
            ->whereIn('personnel_role_groups.group_key', array_keys($this->groups()))
            ->when(
                $this->rolesHaveOrderingColumn(),
                fn ($query) => $query->orderBy('roles.ordering'),
            )
            ->orderBy('roles.name')
            ->get()
            ->groupBy('group_key')
            ->map(fn (Collection $groups): array => $groups->pluck('name')->values()->all())
            ->all();

        $this->roleNamesByGroup = array_replace($emptyGroups, $mappedGroups);

        return $this->roleNamesByGroup;
    }

    /**
     * @param  array<int, string>  $roleNames
     * @return array<int, string>
     */
    protected function orderedRoleNames(array $roleNames): array
    {
        if ($roleNames === []) {
            return [];
        }

        return Role::query()
            ->whereIn('name', $roleNames)
            ->when(
                $this->rolesHaveOrderingColumn(),
                fn ($query) => $query->orderBy('ordering'),
            )
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }

    protected function rolesHaveOrderingColumn(): bool
    {
        if ($this->rolesHaveOrderingColumn !== null) {
            return $this->rolesHaveOrderingColumn;
        }

        return $this->rolesHaveOrderingColumn = Schema::hasColumn('roles', 'ordering');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function childStudyStatuses(): array
    {
        return [
            ['value' => 'in_course', 'label' => __('Studying')],
            ['value' => 'graduated', 'label' => __('Completed')],
        ];
    }
}
