<?php

namespace App\Livewire\Admin\Management\AcademicYear;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AcademicYearIndex extends Component
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
        $this->dispatch('open-create-academic-year-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-year.index');
    }
}
