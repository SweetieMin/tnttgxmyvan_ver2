<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected function modelClass(): string
    {
        return Role::class;
    }

    protected function logName(): string
    {
        return 'roles';
    }

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->withCount(['permissions', 'users', 'manageableRoles'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->paginate($perPage);
    }

    public function findWithPermissions(int $roleId): Role
    {
        /** @var Role */
        return $this->query()
            ->with(['permissions:id,name', 'manageableRoles:id,name', 'personnelRoleGroups:id,role_id,group_key'])
            ->findOrFail($roleId);
    }

    public function findForDelete(int $roleId): Role
    {
        /** @var Role */
        return $this->query()
            ->withCount('users')
            ->findOrFail($roleId);
    }

    /**
     * @param  array<int, string>  $selectedPermissions
     * @param  array<int, int|string>  $selectedManageableRoles
     * @param  array<int, string>  $selectedPersonnelGroups
     */
    public function save(
        string $roleName,
        array $selectedPermissions,
        array $selectedManageableRoles = [],
        array $selectedPersonnelGroups = [],
        ?int $editingRoleId = null,
    ): Role {
        /** @var Role|null $subject */
        $subject = $editingRoleId ? $this->findOrFail($editingRoleId) : null;

        /** @var Role */
        return $this->runInTransaction(
            action: $editingRoleId ? 'update' : 'create',
            subject: $subject,
            properties: [
                'role_name' => $roleName,
                'selected_permissions' => $selectedPermissions,
                'selected_manageable_roles' => $selectedManageableRoles,
                'selected_personnel_groups' => $selectedPersonnelGroups,
            ],
            callback: function () use ($roleName, $selectedPermissions, $selectedManageableRoles, $selectedPersonnelGroups, $editingRoleId): Role {
                /** @var Role $role */
                $role = $editingRoleId
                    ? $this->findOrFail($editingRoleId)
                    : $this->create([
                        'name' => $roleName,
                        'guard_name' => 'web',
                    ]);

                if ($editingRoleId) {
                    /** @var Role $role */
                    $role = $this->update($role, [
                        'name' => $roleName,
                        'guard_name' => 'web',
                    ]);
                }

                $role->syncPermissionsWithActivityLog($selectedPermissions);
                $role->syncManageableRolesWithActivityLog(
                    collect($selectedManageableRoles)->map(fn (mixed $roleId): int => (int) $roleId)->all(),
                );
                $role->syncPersonnelRoleGroupsWithActivityLog($selectedPersonnelGroups);

                return $role;
            },
        );
    }

    public function delete(Model $model): bool
    {
        return $this->runInTransaction(
            action: 'delete',
            subject: $model,
            properties: [
                'role_id' => $model->getKey(),
                'role_name' => $model->getAttribute('name'),
            ],
            callback: fn (): bool => parent::delete($model),
        );
    }
}
