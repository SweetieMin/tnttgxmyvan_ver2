<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => fake()->randomElement(['general', 'branding', 'theme', 'mail', 'system']),
            'key' => fake()->unique()->slug(2, '.'),
            'value' => fake()->sentence(),
            'type' => fake()->randomElement(['string', 'boolean', 'integer', 'json', 'image']),
            'label' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'is_public' => fake()->boolean(),
            'is_encrypted' => false,
            'autoload' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
