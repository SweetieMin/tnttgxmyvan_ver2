<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_date' => fake()->date(),
            'transaction_item' => fake()->randomElement([
                'Đóng học phí giáo lý',
                'Ủng hộ quỹ đoàn',
                'Chi mua tập vở',
                'Chi hỗ trợ sinh hoạt',
            ]),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['income', 'expense']),
            'amount' => fake()->numberBetween(100000, 5000000),
            'file_name' => null,
            'in_charge' => fake()->name(),
            'status' => fake()->randomElement(['pending', 'completed']),
        ];
    }
}
