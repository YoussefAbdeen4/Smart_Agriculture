<?php

namespace Database\Factories;

use App\Models\React;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<React>
 */
class ReactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_like' => fake()->boolean(),
        ];
    }
}
