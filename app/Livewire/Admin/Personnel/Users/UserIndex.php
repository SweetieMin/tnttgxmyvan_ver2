<?php

namespace App\Livewire\Admin\Personnel\Users;

use App\Livewire\Admin\Personnel\PersonnelIndex;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Tất cả user')]
class UserIndex extends PersonnelIndex
{
    public function mount(string $group = 'users'): void
    {
        parent::mount('users');
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.users.user-index');
    }
}
