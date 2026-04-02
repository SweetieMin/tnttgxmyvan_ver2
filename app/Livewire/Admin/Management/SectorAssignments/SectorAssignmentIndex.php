<?php

namespace App\Livewire\Admin\Management\SectorAssignments;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Phân công ngành')]
class SectorAssignmentIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.management.sector-assignments.index');
    }
}
