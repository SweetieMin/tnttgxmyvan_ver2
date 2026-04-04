<?php

namespace App\Livewire\Admin\Personnel;

use App\Foundation\PersonnelDirectory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PersonnelIndex extends Component
{
    public string $group = 'users';

    public string $search = '';

    public int $perPage = 15;

    public string $selectedStatus = '';

    public string $selectedRole = '';

    public function mount(string $group = 'users'): void
    {
        abort_unless($this->directory()->hasGroup($group), 404);

        $this->group = $group;
        $this->selectedStatus = $this->defaultSelectedStatus();
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'perPage', 'selectedStatus', 'selectedRole']);
        $this->perPage = 15;
        $this->selectedStatus = $this->defaultSelectedStatus();
    }

    public function openCreateModal(): void
    {
        abort_unless($this->canCreate(), 403);

        $this->redirectRoute('admin.personnel.create', [
            'group' => $this->creationContext(),
        ], navigate: true);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function statusOptions(): array
    {
        return $this->group === 'children'
            ? $this->directory()->childStudyStatuses()
            : [];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function roleOptions(): array
    {
        if (! $this->directory()->isAllUsersPage($this->group)) {
            return [];
        }

        return collect($this->directory()->manageableRoleNamesFor(auth()->user()))
            ->map(fn (string $roleName): array => [
                'value' => $roleName,
                'label' => $roleName,
            ])
            ->values()
            ->all();
    }

    public function title(): string
    {
        return $this->groupConfig()['label'];
    }

    public function subtitle(): string
    {
        return $this->groupConfig()['subtitle'];
    }

    public function icon(): string
    {
        return $this->groupConfig()['icon'];
    }

    public function canCreate(): bool
    {
        $permission = $this->createPermission();

        if ($permission === null || ! auth()->user()?->can($permission)) {
            return false;
        }

        return $this->directory()->manageableRoleNamesFor(auth()->user(), $this->creationContext()) !== [];
    }

    public function createButtonLabel(): ?string
    {
        return $this->canCreate() ? __('Add profile') : null;
    }

    public function createPermission(): ?string
    {
        return $this->directory()->createPermissionForGroup($this->creationContext());
    }

    /**
     * @return array{
     *     label: string,
     *     icon: string,
     *     route: string,
     *     subtitle: string,
     *     permission: string,
     *     create_permission: string|null,
     *     update_permission: string|null,
     *     delete_permission: string|null,
     *     roles: array<int, string>
     * }
     */
    protected function groupConfig(): array
    {
        return $this->directory()->group($this->group);
    }

    protected function creationContext(): string
    {
        return $this->directory()->isDeletedUsersPage($this->group)
            ? 'users'
            : $this->group;
    }

    protected function defaultSelectedStatus(): string
    {
        return $this->group === 'children' ? 'in_course' : '';
    }

    protected function directory(): PersonnelDirectory
    {
        return app(PersonnelDirectory::class);
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.personnel-index');
    }
}
