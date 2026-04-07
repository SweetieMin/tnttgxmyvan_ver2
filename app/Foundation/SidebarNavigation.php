<?php

namespace App\Foundation;

use App\Models\User;
use Illuminate\Support\Collection;

class SidebarNavigation
{
    /**
     * @return array{
     *     primary: list<array{label: string, items: list<array{icon: string, href: string, label: string, active: bool}>}>,
     *     secondary: list<array{label: string, items: list<array{icon: string, href: string, label: string, active: bool}>}>
     * }
     */
    public function for(?User $user): array
    {
        $permissions = $this->permissionNames($user);

        return [
            'primary' => $this->filteredSections([
                $this->section(__('General'), [
                    $this->item('home', route('dashboard'), 'dashboard', __('Dashboard')),
                ]),
                $this->section(__('Management'), [
                    $permissions->contains('management.academic-year.view')
                        ? $this->item('calendar-days', route('admin.management.academic-years'), 'admin.management.academic-years', __('Academic years'))
                        : null,

                    $permissions->contains('management.program.view')
                        ? $this->item('academic-cap', route('admin.management.programs'), 'admin.management.programs', __('Programs'))
                        : null,
                    $permissions->contains('management.regulation.view')
                        ? $this->item('clipboard-document-list', route('admin.management.regulations'), 'admin.management.regulations', __('Regulations'))
                        : null,
                    $permissions->contains('management.academic-course.view')
                        ? $this->item('rectangle-stack', route('admin.management.academic-courses'), 'admin.management.academic-courses', __('Catechism - sector classes'))
                        : null,
                    $permissions->contains('management.enrollment.view')
                        ? $this->item('clipboard-document-check', route('admin.management.enrollments'), 'admin.management.enrollments', __('Enrollments'))
                        : null,
                    $permissions->contains('management.gradebook.view')
                        ? $this->item('book-open', route('admin.management.gradebooks'), 'admin.management.gradebooks', __('Gradebooks'))
                        : null,
                    $permissions->contains('management.attendance-schedule.view')
                        ? $this->item('calendar-days', route('admin.management.attendance-schedules'), 'admin.management.attendance-schedules', __('Attendance schedules'))
                        : null,
                    $permissions->contains('management.attendance-checkin.view')
                        ? $this->item('qr-code', route('admin.management.attendance-checkins'), 'admin.management.attendance-checkins', __('Attendance check-ins'))
                        : null,
                    $permissions->contains('management.activity-point.view')
                        ? $this->item('sparkles', route('admin.management.activity-points'), 'admin.management.activity-points', __('Activity points'))
                        : null,
                    $permissions->contains('management.promotion.view')
                        ? $this->item('arrow-trending-up', route('admin.management.promotions'), 'admin.management.promotions', __('Promotions'))
                        : null,
                ]),
                $this->section(__('Arrangement'), [
                    $permissions->contains('arrangement.class-assignment.view')
                        ? $this->item('users', route('admin.arrangement.class-assignments'), 'admin.arrangement.class-assignments', __('Class assignments'))
                        : null,
                    $permissions->contains('arrangement.sector-assignment.view')
                        ? $this->item('flag', route('admin.arrangement.sector-assignments'), 'admin.arrangement.sector-assignments', __('Sector assignments'))
                        : null,
                ]),
                $this->section(__('Personnel'), [

                    $permissions->contains('personnel.director.view')
                        ? $this->item('sparkles', route('admin.personnel.directors'), 'admin.personnel.directors*', __('Directors'), 'directors')
                        : null,
                    $permissions->contains('personnel.catechist.view')
                        ? $this->item('book-open', route('admin.personnel.catechists'), 'admin.personnel.catechists*', __('Catechists'), 'catechists')
                        : null,
                    $permissions->contains('personnel.leader.view')
                        ? $this->item('flag', route('admin.personnel.leaders'), 'admin.personnel.leaders*', __('Leaders'), 'leaders')
                        : null,
                    $permissions->contains('personnel.child.view')
                        ? $this->item('users', route('admin.personnel.children'), 'admin.personnel.children*', __('Children'), 'children')
                        : null,
                    $permissions->contains('personnel.user.view')
                        ? $this->item('user-group', route('admin.personnel.users'), 'admin.personnel.users*', __('All users'), 'users')
                        : null,
                    $permissions->contains('personnel.deleted.view')
                        ? $this->item('archive-box-x-mark', route('admin.personnel.deleted-users'), 'admin.personnel.deleted-users*', __('Deleted users'))
                        : null,
                ]),
                $this->section(__('Finance'), [
                    $permissions->contains('finance.category.view')
                        ? $this->item('tag', route('admin.finance.categories'), 'admin.finance.categories', __('Categories'))
                        : null,
                    $permissions->contains('finance.category.view')
                        ? $this->item('presentation-chart-line', route('admin.finance.categories.analytics'), 'admin.finance.categories.analytics', __('Category analytics'))
                        : null,
                    $permissions->contains('finance.transaction.view')
                        ? $this->item('banknotes', route('admin.finance.transactions'), 'admin.finance.transactions*', __('Common fund'))
                        : null,
                ]),
                $this->section(__('Access'), [
                    $permissions->contains('access.role.view')
                        ? $this->item('shield-check', route('admin.access.roles'), 'admin.access.roles', __('Roles'))
                        : null,
                    $permissions->contains('access.permission.view')
                        ? $this->item('key', route('admin.access.permissions'), 'admin.access.permissions', __('Permissions'))
                        : null,
                ]),
            ]),
            'secondary' => $this->filteredSections([
                $this->section(__('Advance'), [
                    $permissions->contains('settings.site.general.view')
                        ? $this->item('cog', route('admin.settings.site.general'), 'admin.settings.site.*', __('System configuration'))
                        : null,
                    $permissions->contains('settings.log.activity.view') || $permissions->contains('settings.log.activity-failed.view')
                        ? $this->item('notebook-pen', route('admin.settings.log.activity'), 'admin.settings.log.*', __('System logs'))
                        : null,
                ]),
            ]),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    protected function permissionNames(?User $user): Collection
    {
        if ($user === null) {
            return collect();
        }

        return $user->getAllPermissions()
            ->pluck('name')
            ->filter(fn (mixed $name): bool => is_string($name) && $name !== '')
            ->values();
    }

    /**
     * @param  list<array{icon: string, href: string, label: string, active: bool}|null>  $items
     * @return array{label: string, items: list<array{icon: string, href: string, label: string, active: bool}>}
     */
    protected function section(string $label, array $items): array
    {
        return [
            'label' => $label,
            'items' => array_values(array_filter($items)),
        ];
    }

    /**
     * @param  string|array<int, string>  $current
     * @return array{icon: string, href: string, label: string, active: bool}
     */
    protected function item(string $icon, string $href, string|array $current, string $label, ?string $personnelGroup = null): array
    {
        return [
            'icon' => $icon,
            'href' => $href,
            'label' => $label,
            'active' => $this->isActiveItem($current, $personnelGroup),
        ];
    }

    /**
     * @param  list<array{label: string, items: list<array{icon: string, href: string, label: string, active: bool}>}>  $sections
     * @return list<array{label: string, items: list<array{icon: string, href: string, label: string, active: bool}>}>
     */
    protected function filteredSections(array $sections): array
    {
        return array_values(array_filter(
            $sections,
            fn (array $section): bool => $section['items'] !== [],
        ));
    }

    /**
     * @param  string|array<int, string>  $current
     */
    protected function isActiveItem(string|array $current, ?string $personnelGroup = null): bool
    {
        if ($personnelGroup === null) {
            return request()->routeIs($current);
        }

        $routeName = request()->route()?->getName();
        $currentGroup = request()->route('group');

        if (in_array($routeName, ['admin.personnel.create', 'admin.personnel.users.edit'], true)) {
            return $currentGroup === $personnelGroup;
        }

        return request()->routeIs($current);
    }
}
