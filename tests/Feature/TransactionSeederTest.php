<?php

use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TransactionSeeder;

test('transaction seeder creates dated transactions for all finance categories', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(TransactionSeeder::class);

    $transactions = Transaction::query()
        ->with('category')
        ->orderBy('transaction_date')
        ->get();

    expect($transactions)->toHaveCount(24);

    expect($transactions->every(function (Transaction $transaction): bool {
        return $transaction->category !== null
            && $transaction->transaction_date !== null
            && $transaction->transaction_date->between(
                CarbonImmutable::parse('2025-01-01'),
                CarbonImmutable::today(),
            )
            && in_array($transaction->type, ['income', 'expense'], true)
            && $transaction->status === 'completed';
    }))->toBeTrue();

    expect($transactions->groupBy('category_id')->map->count()->values()->all())
        ->each->toBe(2);

    expect($transactions->pluck('category.name')->unique()->sort()->values()->all())
        ->toBe(Category::query()->orderBy('name')->pluck('name')->values()->all());
});
