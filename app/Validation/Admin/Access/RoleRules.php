<?php

namespace App\Validation\Admin\Access;

use App\Models\Permission;
use App\Models\Role;
use Closure;
use Illuminate\Validation\Rule;

class RoleRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $roleId = null): array
    {
        $adminRoleId = Role::query()->where('name', 'Admin')->value('id');

        return [
            'roleName' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class, 'name')->ignore($roleId),
            ],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', Rule::exists(Permission::class, 'name')],
            'selectedManageableRoles' => ['array'],
            'selectedManageableRoles.*' => [
                'integer',
                Rule::exists(Role::class, 'id'),
                function (string $attribute, mixed $value, Closure $fail) use ($roleId, $adminRoleId): void {
                    $selectedRoleId = (int) $value;

                    if ($roleId !== null && $selectedRoleId === $roleId) {
                        $fail(__('A role cannot manage itself.'));

                        return;
                    }

                    if ($adminRoleId !== null && $selectedRoleId === (int) $adminRoleId) {
                        $fail(__('The Admin role cannot be managed.'));
                    }
                },
            ],
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
            'selectedManageableRoles.*.exists' => __('Selected managed role is invalid.'),
        ];
    }
}
