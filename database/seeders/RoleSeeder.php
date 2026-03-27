<?php

namespace Database\Seeders;

use App\Models\PersonnelRoleGroup;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [

            ['name' => 'Cha Tuyên Úy', 'group_keys' => ['directors']],
            ['name' => 'Thầy Phó Tế', 'group_keys' => ['directors']],

            ['name' => 'Trưởng Giáo Lý', 'group_keys' => ['catechists']],
            ['name' => 'Phó Giáo Lý', 'group_keys' => ['catechists']],
            ['name' => 'Giáo Lý Viên', 'group_keys' => ['catechists']],

            ['name' => 'Xứ Đoàn Trưởng', 'group_keys' => ['leaders']],
            ['name' => 'Xứ Đoàn Phó', 'group_keys' => ['leaders']],
            ['name' => 'Thủ Quỹ', 'group_keys' => ['leaders']],

            ['name' => 'Trưởng Ngành Nghĩa', 'group_keys' => ['leaders']],
            ['name' => 'Phó Ngành Nghĩa', 'group_keys' => ['leaders']],

            ['name' => 'Trưởng Ngành Thiếu', 'group_keys' => ['leaders']],
            ['name' => 'Phó Ngành Thiếu', 'group_keys' => ['leaders']],

            ['name' => 'Trưởng Ngành Ấu', 'group_keys' => ['leaders']],
            ['name' => 'Phó Ngành Ấu', 'group_keys' => ['leaders']],

            ['name' => 'Trưởng Ngành Tiền Ấu', 'group_keys' => ['leaders']],
            ['name' => 'Phó Ngành Tiền Ấu', 'group_keys' => ['leaders']],

            ['name' => 'Huynh Trưởng', 'group_keys' => ['leaders']],
            ['name' => 'Dự Trưởng', 'group_keys' => ['leaders']],

            ['name' => 'Đội Trưởng', 'group_keys' => ['leaders', 'children']],
            ['name' => 'Thiếu Nhi', 'group_keys' => ['children']],
        ];

        foreach ($roles as $role) {
            $model = Role::findOrCreate($role['name'], 'web');

            PersonnelRoleGroup::query()->where('role_id', $model->id)->delete();

            PersonnelRoleGroup::query()->insert(
                collect($role['group_keys'])
                    ->map(fn (string $groupKey): array => [
                        'role_id' => $model->id,
                        'group_key' => $groupKey,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->all(),
            );
        }
    }
}
