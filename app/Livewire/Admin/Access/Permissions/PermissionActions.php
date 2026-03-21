<?php

namespace App\Livewire\Admin\Access\Permissions;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Validation\Admin\Access\PermissionRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class PermissionActions extends Component
{
    use AuthorizesRequests;

    public bool $showPermissionModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingPermissionId = null;

    public ?int $editingPermissionId = null;

    #[Validate]
    public string $permissionName = '';

    #[Locked]
    public string $originalPermissionName = '';

    #[On('open-create-permission-modal')]
    public function openCreateModal(): void
    {
        $this->authorize('create', Permission::class);
        $this->resetForm();
        $this->showPermissionModal = true;
    }

    #[On('edit-permission')]
    public function openEditModal(int $permissionId): void
    {
        $permission = $this->permissionRepository()->find($permissionId);
        $this->authorize('update', $permission);
        $this->editingPermissionId = (int) $permission->id;
        $this->permissionName = $permission->name;
        $this->syncOriginalFormState();
        $this->showPermissionModal = true;
    }

    public function savePermission(): void
    {
        $isUpdating = $this->editingPermissionId !== null;
        if ($this->editingPermissionId) {
            $this->authorize('update', $this->permissionRepository()->findOrFail($this->editingPermissionId));
        } else {
            $this->authorize('create', Permission::class);
        }

        $validated = $this->validate();

        try {
            $this->permissionRepository()->save(
                $validated['permissionName'],
                $this->editingPermissionId,
            );
        } catch (Throwable $exception) {
            $this->addError('permissionName', __('Permission save failed.'));

            Flux::toast(
                text: __('Permission save failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Flux::toast(
            text: $isUpdating ? __('Permission updated successfully.') : __('Permission created successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('permission-saved');
        $this->closePermissionModal();
    }

    #[On('confirm-delete-permission')]
    public function confirmDeletePermission(int $permissionId): void
    {
        $this->authorize('delete', $this->permissionRepository()->findOrFail($permissionId));
        $this->deletingPermissionId = $permissionId;
        $this->showDeleteModal = true;
    }

    public function deletePermission(): void
    {
        $permission = $this->permissionRepository()->findForDelete($this->deletingPermissionId);
        $this->authorize('delete', $permission);

        if ($permission->roles_count > 0 || $permission->users_count > 0) {
            $this->addError('deletePermission', __('This permission is in use and cannot be deleted.'));

            return;
        }

        try {
            $this->permissionRepository()->delete($permission);
        } catch (Throwable $exception) {
            $this->addError('deletePermission', __('Permission delete failed.'));

            Flux::toast(
                text: __('Permission delete failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Flux::toast(
            text: __('Permission deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('permission-deleted');
        $this->closeDeleteModal();
    }

    public function closePermissionModal(): void
    {
        $this->showPermissionModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPermissionId = null;
        $this->resetErrorBag('deletePermission');
    }

    public function hasPermissionChanges(): bool
    {
        return trim($this->permissionName) !== trim($this->originalPermissionName);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return PermissionRules::rules($this->editingPermissionId);
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return PermissionRules::messages();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingPermissionId', 'permissionName']);
        $this->syncOriginalFormState();
        $this->resetErrorBag();
    }

    protected function syncOriginalFormState(): void
    {
        $this->originalPermissionName = $this->permissionName;
    }

    protected function permissionRepository(): PermissionRepositoryInterface
    {
        return app(PermissionRepositoryInterface::class);
    }

    public function render(): View
    {
        return view('livewire.admin.access.permissions.permission-actions');
    }
}
