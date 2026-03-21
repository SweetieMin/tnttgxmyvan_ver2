<?php

namespace App\Livewire\Admin\Management\Programs;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
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
        $this->dispatch('open-create-program-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.management.programs.index');
    }
}
