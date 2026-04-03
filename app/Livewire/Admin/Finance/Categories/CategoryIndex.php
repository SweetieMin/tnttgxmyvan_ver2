<?php

namespace App\Livewire\Admin\Finance\Categories;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Hạng mục')]
class CategoryIndex extends Component
{
    public function openCreateModal(): void
    {
        $this->dispatch('open-create-category-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.finance.categories.category-index');
    }
}
