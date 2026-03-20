<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('access.permission.view');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('access.permission.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('access.permission.create');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('access.permission.update');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('access.permission.delete');
    }
}
