<?php

namespace App\Livewire\Admin\Access\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Validation\Admin\Access\RoleRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class RoleActions extends Component
{
    use AuthorizesRequests;

    public bool $showRoleModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingRoleId = null;

    public ?int $editingRoleId = null;

    public string $permissionSearch = '';

    #[Validate]
    public string $roleName = '';

    /**
     * @var array<int, string>
     */
    #[Validate]
    public array $selectedPermissions = [];

    /**
     * @var array<int, int>
     */
    #[Validate]
    public array $selectedManageableRoles = [];

    #[Locked]
    public string $originalRoleName = '';

    /**
     * @var array<int, string>
     */
    #[Locked]
    public array $originalSelectedPermissions = [];

    /**
     * @var array<int, int>
     */
    #[Locked]
    public array $originalSelectedManageableRoles = [];

    #[On('open-create-role-modal')]
    public function openCreateModal(): void
    {
        $this->authorize('create', Role::class);
        $this->resetForm();
        $this->showRoleModal = true;
    }

    #[On('edit-role')]
    public function openEditModal(int $roleId): void
    {
        $role = $this->roleRepository()->findWithPermissions($roleId);
        $this->authorize('update', $role);
        $this->editingRoleId = (int) $role->id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions
            ->pluck('name')
            ->values()
            ->all();
        $this->selectedManageableRoles = $role->manageableRoles
            ->pluck('id')
            ->map(fn (mixed $roleId): int => (int) $roleId)
            ->values()
            ->all();
        $this->syncOriginalFormState();
        $this->permissionSearch = '';
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        $isUpdating = $this->editingRoleId !== null;
        if ($this->editingRoleId) {
            $this->authorize('update', $this->roleRepository()->findOrFail($this->editingRoleId));
        } else {
            $this->authorize('create', Role::class);
        }

        $validated = $this->validate();

        try {
            $this->roleRepository()->save(
                $validated['roleName'],
                $validated['selectedPermissions'] ?? [],
                $validated['selectedManageableRoles'] ?? [],
                $this->editingRoleId,
            );
        } catch (Throwable $exception) {
            $this->addError('roleName', __('Role save failed.'));

            Flux::toast(
                text: __('Role save failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Flux::toast(
            text: $isUpdating ? __('Role updated successfully.') : __('Role created successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('role-saved');
        $this->closeRoleModal();
    }

    #[On('confirm-delete-role')]
    public function confirmDeleteRole(int $roleId): void
    {
        $this->authorize('delete', $this->roleRepository()->findOrFail($roleId));
        $this->deletingRoleId = $roleId;
        $this->showDeleteModal = true;
    }

    public function deleteRole(): void
    {
        $role = $this->roleRepository()->findForDelete($this->deletingRoleId);
        $this->authorize('delete', $role);

        if ($role->users_count > 0) {
            $this->addError('deleteRole', __('This role is assigned to users and cannot be deleted.'));

            return;
        }

        try {
            $this->roleRepository()->delete($role);
        } catch (Throwable $exception) {
            $this->addError('deleteRole', __('Role delete failed.'));

            Flux::toast(
                text: __('Role delete failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Flux::toast(
            text: __('Role deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('role-deleted');
        $this->closeDeleteModal();
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingRoleId = null;
        $this->resetErrorBag('deleteRole');
    }

    /**
     * @return Collection<int, Collection<int, Permission>>
     */
    public function groupedPermissions(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->filter(function (Permission $permission): bool {
                return $this->permissionSearch === ''
                    || str_contains(strtolower($permission->name), strtolower($this->permissionSearch))
                    || str_contains(strtolower($this->formatPermissionLabel($permission->name)), strtolower($this->permissionSearch));
            })
            ->groupBy(fn (Permission $permission): string => $this->permissionGroup($permission->name));
    }

    /**
     * @return Collection<int, Role>
     */
    public function manageableRoles(): Collection
    {
        $adminRoleId = Role::query()->where('name', 'Admin')->value('id');

        return Role::query()
            ->orderBy('name')
            ->when(
                $this->editingRoleId !== null,
                fn ($query) => $query->whereKeyNot($this->editingRoleId),
            )
            ->when(
                $adminRoleId !== null,
                fn ($query) => $query->whereKeyNot((int) $adminRoleId),
            )
            ->get();
    }

    public function permissionGroup(string $permission): string
    {
        $parts = explode('.', $permission);

        return str($parts[1] ?? $parts[0] ?? __('General'))
            ->replace('-', ' ')
            ->headline()
            ->toString();
    }

    public function formatPermissionLabel(string $permission): string
    {
        return str($permission)
            ->replace('.', ' ')
            ->replace('-', ' ')
            ->headline()
            ->toString();
    }

    public function hasRoleChanges(): bool
    {
        return trim($this->roleName) !== trim($this->originalRoleName)
            || $this->normalizePermissions($this->selectedPermissions) !== $this->normalizePermissions($this->originalSelectedPermissions)
            || $this->normalizeRoleIds($this->selectedManageableRoles) !== $this->normalizeRoleIds($this->originalSelectedManageableRoles);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return RoleRules::rules($this->editingRoleId);
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return RoleRules::messages();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingRoleId', 'roleName', 'selectedPermissions', 'selectedManageableRoles', 'permissionSearch']);
        $this->syncOriginalFormState();
        $this->resetErrorBag();
    }

    protected function syncOriginalFormState(): void
    {
        $this->originalRoleName = $this->roleName;
        $this->originalSelectedPermissions = $this->normalizePermissions($this->selectedPermissions);
        $this->originalSelectedManageableRoles = $this->normalizeRoleIds($this->selectedManageableRoles);
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array<int, string>
     */
    protected function normalizePermissions(array $permissions): array
    {
        sort($permissions);

        return array_values($permissions);
    }

    /**
     * @param  array<int, int>  $roleIds
     * @return array<int, int>
     */
    protected function normalizeRoleIds(array $roleIds): array
    {
        sort($roleIds);

        return array_values($roleIds);
    }

    protected function roleRepository(): RoleRepositoryInterface
    {
        return app(RoleRepositoryInterface::class);
    }

    public function render(): View
    {
        return view('livewire.admin.access.roles.role-actions', [
            'permissionGroups' => $this->groupedPermissions(),
            'manageableRoles' => $this->manageableRoles(),
        ]);
    }
}
