<?php

namespace App\Livewire\Admin\Personnel\DeletedUsers;

use App\Livewire\Admin\Personnel\PersonnelIndex;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('User bị xoá')]
class DeletedUserIndex extends PersonnelIndex
{
    public function mount(string $group = 'deleted-users'): void
    {
        parent::mount('deleted-users');
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.deleted-users.deleted-user-index');
    }
}
