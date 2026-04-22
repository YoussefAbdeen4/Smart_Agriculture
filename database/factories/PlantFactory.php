<?php

namespace Database\Factories;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plant>
 */
class PlantFactory extends Factory
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
            'img' => null,
            'health_status' => fake()->randomElement(['healthy', 'diseased', 'moderate']),
            'growth_stage' => fake()->randomElement(['seedling', 'vegetative', 'flowering', 'fruiting']),
        ];
    }
}
