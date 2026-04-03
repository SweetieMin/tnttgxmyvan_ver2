<?php

namespace App\Livewire\Admin\Management\Regulations;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Nội quy')]
class RegulationIndex extends Component
{
    public function openCreateModal(): void
    {
        $this->dispatch('open-create-regulation-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.management.regulations.index');
    }
}
