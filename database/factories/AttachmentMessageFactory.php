<?php

namespace Database\Factories;

use App\Models\AttachmentMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttachmentMessage>
 */
class AttachmentMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->filename(),
        ];
    }
}
