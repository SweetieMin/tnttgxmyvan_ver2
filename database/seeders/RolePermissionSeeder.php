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

            'personnel.director.view',
            'personnel.director.create',
            'personnel.director.update',
            'personnel.director.delete',

            'personnel.catechist.view',
            'personnel.catechist.create',
            'personnel.catechist.update',
            'personnel.catechist.delete',

            'personnel.leader.view',
            'personnel.leader.create',
            'personnel.leader.update',
            'personnel.leader.delete',

            'personnel.child.view',
            'personnel.child.create',
            'personnel.child.update',
            'personnel.child.delete',

            'personnel.user.view',
            'personnel.user.create',
            'personnel.user.update',
            'personnel.user.delete',

            'personnel.deleted.view',

            'management.academic-year.view',
            'management.academic-year.create',
            'management.academic-year.update',
            'management.academic-year.delete',

            'management.academic-course.view',
            'management.academic-course.create',
            'management.academic-course.update',
            'management.academic-course.delete',

            'arrangement.enrollment.view',
            'arrangement.enrollment.create',
            'arrangement.enrollment.update',
            'arrangement.enrollment.delete',

            'attendance.gradebook.view',
            'attendance.gradebook.create',
            'attendance.gradebook.update',
            'attendance.gradebook.delete',

            'arrangement.sector-assignment.view',
            'arrangement.sector-assignment.create',
            'arrangement.sector-assignment.update',
            'arrangement.sector-assignment.delete',

            'arrangement.class-assignment.view',
            'arrangement.class-assignment.create',
            'arrangement.class-assignment.update',
            'arrangement.class-assignment.delete',

            'arrangement.attendance-schedule.view',
            'arrangement.attendance-schedule.create',
            'arrangement.attendance-schedule.update',
            'arrangement.attendance-schedule.delete',

            'attendance.attendance-checkin.view',
            'attendance.attendance-checkin.create',
            'attendance.attendance-checkin.update',
            'attendance.attendance-checkin.delete',

            'attendance.activity-point.view',
            'attendance.activity-point.create',
            'attendance.activity-point.update',
            'attendance.activity-point.delete',

            'review.promotion.view',
            'review.promotion.create',
            'review.promotion.update',
            'review.promotion.delete',

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
            'token' => $admin->token ?: Str::random(64),
        ]);

        $admin->deleted_at = null;
        $admin->save();

        $admin->syncRoles([$adminRole]);
    }
}
