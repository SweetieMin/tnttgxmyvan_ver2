<?php

namespace App\Livewire\Admin\Personnel\Leaders;

use App\Livewire\Admin\Personnel\PersonnelIndex;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Huynh trưởng')]
class LeaderIndex extends PersonnelIndex
{
    public function mount(string $group = 'leaders'): void
    {
        parent::mount('leaders');
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.leaders.leader-index');
    }
}
