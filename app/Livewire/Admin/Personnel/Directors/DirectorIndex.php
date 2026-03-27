<?php

namespace App\Livewire\Admin\Personnel\Directors;

use App\Livewire\Admin\Personnel\PersonnelIndex;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Linh hướng')]
class DirectorIndex extends PersonnelIndex
{
    public function mount(string $group = 'directors'): void
    {
        parent::mount('directors');
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.directors.director-index');
    }
}
