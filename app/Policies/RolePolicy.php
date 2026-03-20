<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('access.role.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('access.role.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('access.role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('access.role.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('access.role.delete');
    }
}
