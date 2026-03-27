<?php

namespace App\Livewire\Admin\Personnel\Catechists;

use App\Livewire\Admin\Personnel\PersonnelIndex;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Giáo lý viên')]
class CatechistIndex extends PersonnelIndex
{
    public function mount(string $group = 'catechists'): void
    {
        parent::mount('catechists');
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.catechists.catechist-index');
    }
}
