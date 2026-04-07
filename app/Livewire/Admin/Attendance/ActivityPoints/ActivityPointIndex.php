<?php

namespace App\Livewire\Admin\Attendance\ActivityPoints;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Điểm sinh hoạt')]
class ActivityPointIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.attendance.activity-points.index');
    }
}
