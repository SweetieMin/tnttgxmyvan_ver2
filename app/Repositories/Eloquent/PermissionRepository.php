<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    protected function modelClass(): string
    {
        return Permission::class;
    }

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->withCount(['roles', 'users'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $permissionId): Permission
    {
        /** @var Permission */
        return $this->findOrFail($permissionId);
    }

    public function findForDelete(int $permissionId): Permission
    {
        /** @var Permission */
        return $this->query()
            ->withCount(['roles', 'users'])
            ->findOrFail($permissionId);
    }

    public function save(string $permissionName, ?int $editingPermissionId = null): Permission
    {
        /** @var Permission $permission */
        $permission = $editingPermissionId
            ? $this->findOrFail($editingPermissionId)
            : $this->create([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

        if ($editingPermissionId) {
            /** @var Permission $permission */
            $permission = $this->update($permission, [
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        return $permission;
    }

    public function delete(Model $model): bool
    {
        return parent::delete($model);
    }
}
