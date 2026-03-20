<?php

namespace App\Livewire\Admin\Access\Permissions;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public function mount(string $search = '', int $perPage = 15): void
    {
        $this->search = $search;
        $this->perPage = $perPage;
    }

    #[On('permission-saved')]
    #[On('permission-deleted')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function placeholder(): string
    {
        return view('components.placeholder.permission-list')->render();
    }

    public function render(): View
    {
        return view('livewire.admin.access.permissions.permission-list', [
            'permissions' => $this->permissionRepository()->paginateForAdmin($this->search, $this->perPage),
        ]);
    }

    protected function permissionRepository(): PermissionRepositoryInterface
    {
        return app(PermissionRepositoryInterface::class);
    }
}
