<?php

namespace App\Livewire\Admin\Gradebook\Enrollments;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Ghi danh thiếu nhi')]
class EnrollmentIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.gradebook.enrollments.index');
    }
}
