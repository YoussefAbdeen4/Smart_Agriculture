<?php

namespace Database\Factories;

use App\Models\AIRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AIRequest>
 */
class AIRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_type' => fake()->randomElement(['analysis', 'prediction', 'recommendation']),
            'request_data' => fake()->json(),
            'response_data' => null,
        ];
    }
}
