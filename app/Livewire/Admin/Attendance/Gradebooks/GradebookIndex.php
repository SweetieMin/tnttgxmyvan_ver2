<?php

namespace App\Livewire\Admin\Attendance\Gradebooks;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sổ điểm giáo lý')]
class GradebookIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.attendance.gradebooks.index');
    }
}
