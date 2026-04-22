<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' Plan',
            'irrigation_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'fertilization_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
