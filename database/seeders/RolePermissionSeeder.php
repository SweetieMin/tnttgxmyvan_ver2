<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'access.impersonate.users',

            'access.role.view',
            'access.role.create',
            'access.role.update',
            'access.role.delete',

            'access.permission.view',
            'access.permission.create',
            'access.permission.update',
            'access.permission.delete',

            'management.academic-year.view',
            'management.academic-year.create',
            'management.academic-year.update',
            'management.academic-year.delete',

            'management.academic-course.view',
            'management.academic-course.create',
            'management.academic-course.update',
            'management.academic-course.delete',

            'management.program.view',
            'management.program.create',
            'management.program.update',
            'management.program.delete',

            'management.regulation.view',
            'management.regulation.create',
            'management.regulation.update',
            'management.regulation.delete',

            'finance.transaction.view',
            'finance.transaction.create',
            'finance.transaction.update',
            'finance.transaction.delete',

            'finance.category.view',
            'finance.category.create',
            'finance.category.update',
            'finance.category.delete',

            'settings.site.general.view',
            'settings.site.general.update',

            'settings.site.email.view',
            'settings.site.email.update',

            'settings.site.maintenance.view',
            'settings.site.maintenance.update',

            'settings.site.theme.view',
            'settings.site.theme.update',

            'settings.site.ai-agent.view',
            'settings.site.ai-agent.update',

            'settings.log.activity.view',
            'settings.log.activity-failed.view',

        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::findOrCreate('Admin', 'web');
        $adminRole->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->get()
        );

        $admin = User::withTrashed()
            ->where('username', 'MV21081010')
            ->orWhere('email', 'tntt.myvan@gmail.com')
            ->first() ?? new User;

        $admin->fill([
            'christian_name' => 'Giuse',
            'last_name' => 'Đặng Đình',
            'name' => 'Viên',
            'birthday' => '2010-08-21',
            'username' => 'MV21081010',
            'email' => 'tntt.myvan@gmail.com',
            'password' => '12345',
            'status_login' => 'active',
            'token' => $admin->token ?: Str::random(60),
        ]);

        $admin->deleted_at = null;
        $admin->save();

        $admin->syncRoles([$adminRole]);
    }
}
