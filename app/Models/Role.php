<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
                    'attached_manageable_role_ids' => $attachedManageableRoleIds,
                    'detached_manageable_role_ids' => $detachedManageableRoleIds,
                ],
            ])
            ->log(class_basename($this).' updated');
    }
}
