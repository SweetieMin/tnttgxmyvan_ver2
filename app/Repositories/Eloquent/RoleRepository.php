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

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->withCount(['permissions', 'users'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->paginate($perPage);
    }

    public function findWithPermissions(int $roleId): Role
    {
        /** @var Role */
        return $this->query()
            ->with('permissions:id,name')
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
     */
    public function save(string $roleName, array $selectedPermissions, ?int $editingRoleId = null): Role
    {
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

        $role->syncPermissions($selectedPermissions);

        return $role;
    }

    public function delete(Model $model): bool
    {
        return parent::delete($model);
    }
}
