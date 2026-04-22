<?php

namespace Database\Factories;

use App\Models\AttachmentBlog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttachmentBlog>
 */
class AttachmentBlogFactory extends Factory
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
