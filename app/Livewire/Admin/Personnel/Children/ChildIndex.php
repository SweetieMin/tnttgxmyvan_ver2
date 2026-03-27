<?php

namespace App\Livewire\Admin\Personnel\Children;

use App\Livewire\Admin\Personnel\PersonnelIndex;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Thiếu nhi')]
class ChildIndex extends PersonnelIndex
{
    public function mount(string $group = 'children'): void
    {
        parent::mount('children');
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.children.child-index');
    }
}
