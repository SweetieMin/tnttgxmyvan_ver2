<?php

namespace App\Validation\Admin\Access;

use App\Models\Permission;
use Illuminate\Validation\Rule;

class PermissionRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $permissionId = null): array
    {
        return [
            'permissionName' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Permission::class, 'name')->ignore($permissionId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'permissionName.required' => __('Permission name is required.'),
            'permissionName.unique' => __('This permission already exists.'),
        ];
    }
}
