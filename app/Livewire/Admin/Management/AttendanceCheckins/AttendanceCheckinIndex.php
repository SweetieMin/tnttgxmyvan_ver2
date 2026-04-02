<?php

namespace App\Livewire\Admin\Management\AttendanceCheckins;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Điểm danh sinh hoạt')]
class AttendanceCheckinIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.management.attendance-checkins.index');
    }
}
