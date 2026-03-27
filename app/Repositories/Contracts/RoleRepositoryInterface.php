<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface RoleRepositoryInterface
{
    public function query(): Builder;

    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator;

    public function findWithPermissions(int $roleId): Role;

    public function findForDelete(int $roleId): Role;

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
    ): Role;
}
