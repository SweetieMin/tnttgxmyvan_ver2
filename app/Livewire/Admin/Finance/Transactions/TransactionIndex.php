<?php

namespace App\Livewire\Admin\Finance\Transactions;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Quỹ chung')]
class TransactionIndex extends Component
{
    public function openCreateModal(): void
    {
        $this->dispatch('open-create-transaction-modal');
    }

    public function render(): View
    {
        return view('livewire.admin.finance.transactions.transaction-index');
    }
}
