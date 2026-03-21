<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface TransactionRepositoryInterface
{
    public function query(): Builder;

    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;

    public function paginateForAdmin(string $search, int $perPage, string $type = '', string $status = ''): LengthAwarePaginator;

    public function find(int $transactionId): Transaction;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingTransactionId = null): Transaction;
}
