<?php

namespace Database\Factories;

use App\Models\ActivityFailedLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityFailedLog>
 */
class ActivityFailedLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'log_name' => fake()->randomElement(['roles', 'permissions', 'settings']),
            'action' => fake()->randomElement(['create', 'update', 'delete']),
            'subject_type' => null,
            'subject_id' => null,
            'causer_type' => null,
            'causer_id' => null,
            'message' => fake()->sentence(),
            'exception' => null,
            'properties' => [],
        ];
    }
}
