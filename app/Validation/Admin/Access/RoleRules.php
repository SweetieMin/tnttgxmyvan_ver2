<?php

namespace App\Validation\Admin\Access;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Validation\Rule;

class RoleRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $roleId = null): array
    {
        return [
            'roleName' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class, 'name')->ignore($roleId),
            ],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', Rule::exists(Permission::class, 'name')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'roleName.required' => __('Role name is required.'),
            'roleName.unique' => __('This role name already exists.'),
        ];
    }
}
