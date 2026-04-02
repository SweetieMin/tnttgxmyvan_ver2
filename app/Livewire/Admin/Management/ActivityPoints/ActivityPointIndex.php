<?php

namespace App\Livewire\Admin\Management\ActivityPoints;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Điểm sinh hoạt')]
class ActivityPointIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.management.activity-points.index');
    }
}
