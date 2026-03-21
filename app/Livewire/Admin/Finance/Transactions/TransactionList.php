<?php

namespace App\Livewire\Admin\Finance\Transactions;

use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use WithoutUrlPagination;
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public string $selectedType = '';

    public string $selectedStatus = '';

    public function mount(
        string $search = '',
        int $perPage = 15,
        string $selectedType = '',
        string $selectedStatus = '',
    ): void {
        $this->search = $search;
        $this->perPage = $perPage;
        $this->selectedType = $selectedType;
        $this->selectedStatus = $selectedStatus;
    }

    #[On('transaction-saved')]
    #[On('transaction-deleted')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedType(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    protected function transactionRepository(): TransactionRepositoryInterface
    {
        return app(TransactionRepositoryInterface::class);
    }

    public function render(): View
    {
        return view('livewire.admin.finance.transactions.transaction-list', [
            'transactions' => $this->transactionRepository()->paginateForAdmin(
                $this->search,
                $this->perPage,
                $this->selectedType,
                $this->selectedStatus,
            ),
        ]);
    }
}
