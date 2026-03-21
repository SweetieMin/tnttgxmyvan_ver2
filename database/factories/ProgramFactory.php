<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $ordering = 1;

        return [
            'ordering' => $ordering++,
            'course' => 'Khóa '.$this->faker->unique()->numberBetween(1, 99),
            'sector' => 'Ngành '.$this->faker->unique()->numberBetween(1, 99),
        ];
    }
}
