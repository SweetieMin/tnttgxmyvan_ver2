<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use App\Foundation\PersonnelDirectory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use LogsModelActivity;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function manageableRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'role_manageable_roles',
            'manager_role_id',
            'manageable_role_id',
        )->withTimestamps();
    }

    public function managedByRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'role_manageable_roles',
            'manageable_role_id',
            'manager_role_id',
        )->withTimestamps();
    }

    public function personnelRoleGroups(): HasMany
    {
        return $this->hasMany(PersonnelRoleGroup::class);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function syncPermissionsWithActivityLog(array $permissions): void
    {
        $originalPermissions = $this->permissions()
            ->pluck('name')
            ->sort()
            ->values()
            ->all();

        $normalizedPermissions = collect($permissions)
            ->sort()
            ->values()
            ->all();

        $this->syncPermissions($permissions);

        $attachedPermissions = array_values(array_diff($normalizedPermissions, $originalPermissions));
        $detachedPermissions = array_values(array_diff($originalPermissions, $normalizedPermissions));

        if ($attachedPermissions === [] && $detachedPermissions === []) {
            return;
        }

        activity($this->getTable())
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->event('updated')
            ->withProperties([
                'attributes' => [
                    'attached_permissions' => $attachedPermissions,
                    'detached_permissions' => $detachedPermissions,
                ],
            ])
            ->log(class_basename($this).' updated');
    }

    /**
     * @param  array<int, int>  $manageableRoleIds
     */
    public function syncManageableRolesWithActivityLog(array $manageableRoleIds): void
    {
        $originalManageableRoleIds = $this->manageableRoles()
            ->pluck('roles.id')
            ->map(fn (mixed $roleId): int => (int) $roleId)
            ->sort()
            ->values()
            ->all();

        $normalizedManageableRoleIds = collect($manageableRoleIds)
            ->map(fn (mixed $roleId): int => (int) $roleId)
            ->sort()
            ->values()
            ->all();

        $this->manageableRoles()->sync($manageableRoleIds);

        $attachedManageableRoleIds = array_values(array_diff($normalizedManageableRoleIds, $originalManageableRoleIds));
        $detachedManageableRoleIds = array_values(array_diff($originalManageableRoleIds, $normalizedManageableRoleIds));

        if ($attachedManageableRoleIds === [] && $detachedManageableRoleIds === []) {
            return;
        }

        activity($this->getTable())
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->event('updated')
            ->withProperties([
                'attributes' => [
                    'attached_manageable_roles' => $this->manageableRoleNames($attachedManageableRoleIds),
                    'detached_manageable_roles' => $this->manageableRoleNames($detachedManageableRoleIds),
                ],
            ])
            ->log(class_basename($this).' updated');
    }

    /**
     * @param  array<int, string>  $groupKeys
     */
    public function syncPersonnelRoleGroupsWithActivityLog(array $groupKeys): void
    {
        $originalGroupKeys = $this->personnelRoleGroups()
            ->pluck('group_key')
            ->filter(fn (mixed $groupKey): bool => is_string($groupKey) && $groupKey !== '')
            ->sort()
            ->values()
            ->all();

        $normalizedGroupKeys = collect($groupKeys)
            ->filter(fn (mixed $groupKey): bool => is_string($groupKey) && $groupKey !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($originalGroupKeys === $normalizedGroupKeys) {
            return;
        }

        $this->personnelRoleGroups()->delete();

        $this->personnelRoleGroups()->createMany(
            collect($normalizedGroupKeys)
                ->map(fn (string $groupKey): array => ['group_key' => $groupKey])
                ->all(),
        );

        activity($this->getTable())
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->event('updated')
            ->withProperties([
                'attributes' => [
                    'attached_personnel_groups' => $this->personnelGroupLabels(array_values(array_diff($normalizedGroupKeys, $originalGroupKeys))),
                    'detached_personnel_groups' => $this->personnelGroupLabels(array_values(array_diff($originalGroupKeys, $normalizedGroupKeys))),
                ],
            ])
            ->log(class_basename($this).' updated');
    }

    /**
     * @param  array<int, int>  $roleIds
     * @return array<int, string>
     */
    protected function manageableRoleNames(array $roleIds): array
    {
        /** @var Collection<int, string> $manageableRoleNames */
        $manageableRoleNames = self::query()
            ->whereKey($roleIds)
            ->pluck('name', 'id');

        return collect($roleIds)
            ->map(fn (int $roleId): string => (string) $manageableRoleNames->get($roleId, (string) $roleId))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $groupKeys
     * @return array<int, string>
     */
    protected function personnelGroupLabels(array $groupKeys): array
    {
        return collect($groupKeys)
            ->map(fn (string $groupKey): string => $this->personnelGroupLabel($groupKey))
            ->values()
            ->all();
    }

    protected function personnelGroupLabel(string $groupKey): string
    {
        $directory = app(PersonnelDirectory::class);

        if (! $directory->isGroupPage($groupKey)) {
            return $groupKey;
        }

        return $directory->group($groupKey)['label'];
    }
}
