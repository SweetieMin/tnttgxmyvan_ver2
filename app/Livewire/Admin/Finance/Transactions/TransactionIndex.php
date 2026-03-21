<?php

namespace App\Livewire\Admin\Finance\Transactions;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class TransactionIndex extends Component
{
    public string $search = '';

    public int $perPage = 15;

    public string $selectedType = '';

    public string $selectedStatus = '';

    public function resetFilter(): void
    {
        $this->reset([
            'search',
            'perPage',
            'selectedType',
            'selectedStatus',
        ]);

        $this->perPage = 15;
    }

    public function openCreateModal(): void
    {
        $this->dispatch('open-create-transaction-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.finance.transactions.transaction-index');
    }
}
