<?php

namespace App\Livewire\Admin\Access\Permissions;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quyền')]
class PermissionIndex extends Component
{
    public string $search = '';

    public int $perPage = 15;

    public function resetFilter(): void
    {
        $this->reset(['search', 'perPage']);
        $this->perPage = 15;
    }

    public function openCreateModal(): void
    {
        $this->dispatch('open-create-permission-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.access.permissions.index');
    }
}
