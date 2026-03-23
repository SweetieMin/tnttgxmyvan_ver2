<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    protected function modelClass(): string
    {
        return Transaction::class;
    }

    protected function logName(): string
    {
        return 'transactions';
    }

    public function paginateForAdmin(
        string $search,
        int $perPage,
        string $type = '',
        string $categoryId = '',
        string $status = '',
    ): LengthAwarePaginator {
        return $this->query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('transaction_item', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('in_charge', 'like', '%'.$search.'%')
                        ->orWhereHas('category', function ($categoryQuery) use ($search): void {
                            $categoryQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $transactionId): Transaction
    {
        /** @var Transaction */
        return $this->findOrFail($transactionId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingTransactionId = null): Transaction
    {
        /** @var Transaction|null $subject */
        $subject = $editingTransactionId ? $this->find($editingTransactionId) : null;

        /** @var Transaction */
        return $this->runInTransaction(
            action: $editingTransactionId ? 'update' : 'create',
            subject: $subject,
            properties: $attributes,
            callback: function () use ($attributes, $editingTransactionId): Transaction {
                /** @var Transaction $transaction */
                $transaction = $editingTransactionId
                    ? $this->find($editingTransactionId)
                    : $this->create($attributes);

                if ($editingTransactionId) {
                    /** @var Transaction $transaction */
                    $transaction = $this->update($transaction, $attributes);
                }

                return $transaction;
            },
        );
    }

    public function delete(Model $model): bool
    {
        return $this->runInTransaction(
            action: 'delete',
            subject: $model,
            properties: [
                'transaction_id' => $model->getKey(),
                'category_id' => $model->getAttribute('category_id'),
                'transaction_item' => $model->getAttribute('transaction_item'),
                'type' => $model->getAttribute('type'),
                'amount' => $model->getAttribute('amount'),
            ],
            callback: fn (): bool => parent::delete($model),
        );
    }
}
