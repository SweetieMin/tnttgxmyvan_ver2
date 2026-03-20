<?php

namespace App\Livewire\Admin\Access\Permissions;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;

class PermissionActions extends Component
{
    use AuthorizesRequests;

    public bool $showPermissionModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingPermissionId = null;

    public ?int $editingPermissionId = null;

    public string $permissionName = '';

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

        $validated = $this->validate([
            'permissionName' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Permission::class, 'name')->ignore($this->editingPermissionId),
            ],
        ], [
            'permissionName.required' => __('Permission name is required.'),
            'permissionName.unique' => __('This permission already exists.'),
        ]);

        $this->permissionRepository()->save(
            $validated['permissionName'],
            $this->editingPermissionId,
        );

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

        $this->permissionRepository()->delete($permission);

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

    protected function resetForm(): void
    {
        $this->reset(['editingPermissionId', 'permissionName']);
        $this->resetErrorBag();
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
