<?php

namespace App\Livewire\Admin\Management\Regulations;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class RegulationIndex extends Component
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
        $this->dispatch('open-create-regulation-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.management.regulations.index');
    }
}
