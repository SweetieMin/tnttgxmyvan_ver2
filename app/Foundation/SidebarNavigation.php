<?php

namespace App\Foundation;

use App\Models\User;
use Illuminate\Support\Collection;

class SidebarNavigation
{
    /**
     * @return array{
     *     primary: list<array{label: string, items: list<array{icon: string, href: string, current: string, label: string}>}>,
     *     secondary: list<array{label: string, items: list<array{icon: string, href: string, current: string, label: string}>}>
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
     * @param  list<array{icon: string, href: string, current: string, label: string}|null>  $items
     * @return array{label: string, items: list<array{icon: string, href: string, current: string, label: string}>}
     */
    protected function section(string $label, array $items): array
    {
        return [
            'label' => $label,
            'items' => array_values(array_filter($items)),
        ];
    }

    /**
     * @return array{icon: string, href: string, current: string, label: string}
     */
    protected function item(string $icon, string $href, string $current, string $label): array
    {
        return [
            'icon' => $icon,
            'href' => $href,
            'current' => $current,
            'label' => $label,
        ];
    }

    /**
     * @param  list<array{label: string, items: list<array{icon: string, href: string, current: string, label: string}>}>  $sections
     * @return list<array{label: string, items: list<array{icon: string, href: string, current: string, label: string}>}>
     */
    protected function filteredSections(array $sections): array
    {
        return array_values(array_filter(
            $sections,
            fn (array $section): bool => $section['items'] !== [],
        ));
    }
}
