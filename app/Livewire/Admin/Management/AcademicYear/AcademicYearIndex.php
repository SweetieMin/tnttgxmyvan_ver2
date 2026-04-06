<?php

namespace App\Livewire\Admin\Management\AcademicYear;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Niên khoá')]
class AcademicYearIndex extends Component
{
    public function openCreateModal(): void
    {
        $this->dispatch('open-create-academic-year-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-year.index');
    }
}
