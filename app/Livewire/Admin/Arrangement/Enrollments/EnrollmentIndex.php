<?php

namespace App\Livewire\Admin\Arrangement\Enrollments;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Ghi danh thiếu nhi')]
class EnrollmentIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.arrangement.enrollments.index');
    }
}
