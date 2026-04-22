<?php

namespace Database\Factories;

use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Farm>
 */
class FarmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'location' => fake()->address(),
            'area' => fake()->randomFloat(2, 1, 1000),
            'soil_type' => fake()->randomElement(['loamy', 'sandy', 'clay', 'silty']),
            'img' => null,
        ];
    }
}
