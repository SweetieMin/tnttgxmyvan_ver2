<?php

namespace Database\Factories;

use App\Models\Regulation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Regulation>
 */
class RegulationFactory extends Factory
{
    protected $model = Regulation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $ordering = 1;

        return [
            'ordering' => $ordering++,
            'short_desc' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['plus', 'minus']),
            'status' => $this->faker->randomElement(['applied', 'not_applied', 'pending']),
            'points' => $this->faker->numberBetween(1, 20),
        ];
    }
}
