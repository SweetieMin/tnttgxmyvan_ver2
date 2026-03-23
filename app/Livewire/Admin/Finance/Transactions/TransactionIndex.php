<?php

namespace App\Livewire\Admin\Finance\Transactions;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quỹ chung')]
class TransactionIndex extends Component
{
    public string $search = '';

    public int $perPage = 15;

    public string $selectedType = '';

    public string $selectedCategory = '';

    public string $selectedStatus = '';

    public function resetFilter(): void
    {
        $this->reset([
            'search',
            'perPage',
            'selectedType',
            'selectedCategory',
            'selectedStatus',
        ]);

        $this->perPage = 15;
    }

    public function openCreateModal(): void
    {
        $this->dispatch('open-create-transaction-modal');
    }

    public function exportData(): void
    {
        $this->dispatch(
            'open-transaction-export-modal',
            selectedType: $this->selectedType,
            selectedCategory: $this->selectedCategory,
            selectedStatus: $this->selectedStatus,
        );
    }

    public function render(): View
    {
        return view('livewire.admin.finance.transactions.transaction-index', [
            'categories' => Category::query()
                ->orderBy('ordering')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    'value' => (string) $category->id,
                    'label' => $category->name,
                ])
                ->all(),
        ]);
    }
}
