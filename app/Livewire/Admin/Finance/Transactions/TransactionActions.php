<?php

namespace App\Livewire\Admin\Finance\Transactions;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Validation\Admin\Finance\TransactionRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

class TransactionActions extends Component
{
    use WithFileUploads;

    public bool $showTransactionModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingTransactionId = null;

    public ?int $deletingTransactionId = null;

    #[Validate]
    public string $transaction_date = '';

    #[Validate]
    public string $transaction_item = '';

    #[Validate]
    public string $description = '';

    #[Validate]
    public string $type = 'income';

    #[Validate]
    public int|string $amount = '';

    #[Validate]
    public ?TemporaryUploadedFile $attachment = null;

    #[Validate]
    public string $in_charge = '';

    #[Validate]
    public string $status = 'pending';

    public ?string $existingAttachment = null;

    public bool $removeCurrentAttachment = false;

    #[Locked]
    public array $originalTransactionState = [];

    #[On('open-create-transaction-modal')]
    public function openCreateModal(): void
    {
        $this->ensureCan('finance.transaction.create');
        $this->resetForm();
        $this->showTransactionModal = true;
    }

    #[On('edit-transaction')]
    public function openEditModal(int $transactionId): void
    {
        $this->ensureCan('finance.transaction.update');

        $transaction = $this->transactionRepository()->find($transactionId);

        $this->editingTransactionId = (int) $transaction->id;
        $this->transaction_date = $transaction->transaction_date?->format('Y-m-d') ?? '';
        $this->transaction_item = $transaction->transaction_item;
        $this->description = (string) ($transaction->description ?? '');
        $this->type = $transaction->type;
        $this->amount = $transaction->amount;
        $this->in_charge = (string) ($transaction->in_charge ?? '');
        $this->status = $transaction->status;
        $this->existingAttachment = $transaction->file_name;
        $this->removeCurrentAttachment = false;
        $this->syncOriginalTransactionState();
        $this->showTransactionModal = true;
    }

    public function saveTransaction(): void
    {
        $this->ensureCan($this->editingTransactionId ? 'finance.transaction.update' : 'finance.transaction.create');

        $validated = $this->validate();

        $oldAttachmentPath = $this->editingTransactionId ? $this->existingAttachment : null;
        $newAttachmentPath = null;

        if ($this->attachment) {
            $newAttachmentPath = $this->storeAttachment($this->attachment);
        }

        $payload = [
            'transaction_date' => $validated['transaction_date'],
            'transaction_item' => $validated['transaction_item'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'type' => $validated['type'],
            'amount' => (int) $validated['amount'],
            'file_name' => $newAttachmentPath
                ?? ($this->removeCurrentAttachment ? null : $oldAttachmentPath),
            'in_charge' => $validated['in_charge'] !== '' ? $validated['in_charge'] : null,
            'status' => $validated['status'],
        ];

        try {
            $transaction = $this->transactionRepository()->save($payload, $this->editingTransactionId);
        } catch (Throwable $exception) {
            if ($newAttachmentPath !== null) {
                $this->deleteStoredFile($newAttachmentPath);
            }

            $this->addError('transaction_item', __('Transaction save failed.'));

            Flux::toast(
                text: __('Transaction save failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        if ($newAttachmentPath !== null && $oldAttachmentPath && $oldAttachmentPath !== $newAttachmentPath) {
            $this->deleteStoredFile($oldAttachmentPath);
        }

        if ($newAttachmentPath === null && $this->removeCurrentAttachment && $oldAttachmentPath) {
            $this->deleteStoredFile($oldAttachmentPath);
        }

        $this->existingAttachment = $transaction->file_name;

        Flux::toast(
            text: $this->editingTransactionId ? __('Transaction updated successfully.') : __('Transaction created successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('transaction-saved');
        $this->closeTransactionModal();
    }

    #[On('confirm-delete-transaction')]
    public function confirmDeleteTransaction(int $transactionId): void
    {
        $this->ensureCan('finance.transaction.delete');
        $this->deletingTransactionId = $transactionId;
        $this->showDeleteModal = true;
    }

    public function deleteTransaction(): void
    {
        $this->ensureCan('finance.transaction.delete');

        $transaction = $this->transactionRepository()->find($this->deletingTransactionId);
        $attachmentPath = $transaction->file_name;

        try {
            $this->transactionRepository()->delete($transaction);
        } catch (Throwable $exception) {
            $this->addError('deleteTransaction', __('Transaction delete failed.'));

            Flux::toast(
                text: __('Transaction delete failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        $this->deleteStoredFile($attachmentPath);

        Flux::toast(
            text: __('Transaction deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('transaction-deleted');
        $this->closeDeleteModal();
    }

    public function removeSelectedAttachment(): void
    {
        $this->attachment?->delete();
        $this->reset('attachment');
        $this->resetErrorBag('attachment');
    }

    public function removeExistingAttachment(): void
    {
        $this->removeCurrentAttachment = true;
    }

    public function undoRemoveExistingAttachment(): void
    {
        $this->removeCurrentAttachment = false;
    }

    public function hasTransactionChanges(): bool
    {
        return $this->currentTransactionState() !== $this->originalTransactionState;
    }

    public function shouldShowSaveTransactionButton(): bool
    {
        return $this->hasTransactionChanges();
    }

    /**
     * @return array<int, string>
     */
    public function transactionItemSuggestions(): array
    {
        return Transaction::query()
            ->select('transaction_item')
            ->whereNotNull('transaction_item')
            ->where('transaction_item', '!=', '')
            ->distinct()
            ->latest('id')
            ->limit(20)
            ->pluck('transaction_item')
            ->all();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return TransactionRules::rules();
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return TransactionRules::messages();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingTransactionId',
            'transaction_date',
            'transaction_item',
            'description',
            'type',
            'amount',
            'attachment',
            'in_charge',
            'status',
            'existingAttachment',
            'removeCurrentAttachment',
        ]);

        $this->transaction_date = now()->format('Y-m-d');
        $this->type = 'income';
        $this->status = 'pending';
        $this->syncOriginalTransactionState();
        $this->resetErrorBag();
    }

    protected function syncOriginalTransactionState(): void
    {
        $this->originalTransactionState = $this->currentTransactionState();
    }

    /**
     * @return array<string, mixed>
     */
    protected function currentTransactionState(): array
    {
        return [
            'transaction_date' => $this->transaction_date,
            'transaction_item' => $this->transaction_item,
            'description' => $this->description,
            'type' => $this->type,
            'amount' => $this->amount,
            'in_charge' => $this->in_charge,
            'status' => $this->status,
            'attachment' => $this->attachment?->getClientOriginalName(),
            'existingAttachment' => $this->existingAttachment,
            'removeCurrentAttachment' => $this->removeCurrentAttachment,
        ];
    }

    protected function transactionRepository(): TransactionRepositoryInterface
    {
        return app(TransactionRepositoryInterface::class);
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless((bool) Auth::user()?->can($permission), 403);
    }

    protected function storeAttachment(TemporaryUploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        $filename = sprintf(
            'TRANSACTION-%s-%s.%s',
            now()->format('YmdHis'),
            Str::lower(Str::random(8)),
            $extension,
        );

        return $file->storeAs('files/transactions', $filename, 'public');
    }

    protected function deleteStoredFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function closeTransactionModal(): void
    {
        $this->showTransactionModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingTransactionId = null;
        $this->resetErrorBag('deleteTransaction');
    }

    public function render(): View
    {
        return view('livewire.admin.finance.transactions.transaction-actions');
    }
}
