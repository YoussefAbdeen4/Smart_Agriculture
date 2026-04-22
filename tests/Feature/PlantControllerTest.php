<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlantControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->farm = Farm::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_index_returns_all_plants_for_farm(): void
    {
        Plant::factory(3)->create(['farm_id' => $this->farm->id]);

        $response = $this->actingAs($this->user)->getJson("/api/farms/{$this->farm->id}/plants");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'plants' => [
                        '*' => [
                            'id',
                            'name',
                            'health_status',
                            'growth_stage',
                            'farm_id',
                            'plans',
                        ],
                    ],
                ],
            ]);
    }

    public function test_store_creates_new_plant(): void
    {
        $data = [
            'name' => 'Tomato Plant',
            'health_status' => 'Healthy',
            'growth_stage' => 'Flowering',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/farms/{$this->farm->id}/plants", $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'plant' => [
                        'id',
                        'name',
                        'health_status',
                        'growth_stage',
                        'farm_id',
                        'plans',
                    ],
                ],
            ])
            ->assertJsonFragment(['name' => 'Tomato Plant']);

        $this->assertDatabaseHas('plants', [
            'name' => 'Tomato Plant',
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/farms/{$this->farm->id}/plants", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'health_status', 'growth_stage']);
    }

    public function test_show_returns_specific_plant(): void
    {
        $plant = Plant::factory()->create(['farm_id' => $this->farm->id]);

        $response = $this->actingAs($this->user)->getJson("/api/farms/{$this->farm->id}/plants/{$plant->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $plant->name]);
    }

    public function test_show_returns_not_found_for_plant_not_in_farm(): void
    {
        $otherUser = User::factory()->create();
        $otherFarm = Farm::factory()->create(['user_id' => $otherUser->id]);
        $plant = Plant::factory()->create(['farm_id' => $otherFarm->id]);

        $response = $this->actingAs($this->user)->getJson("/api/farms/{$this->farm->id}/plants/{$plant->id}");

        $response->assertStatus(404);
    }

    public function test_update_modifies_plant(): void
    {
        $plant = Plant::factory()->create(['farm_id' => $this->farm->id]);

        $data = [
            'name' => 'Updated Tomato',
            'health_status' => 'Diseased',
        ];

        $response = $this->actingAs($this->user)->patchJson("/api/farms/{$this->farm->id}/plants/{$plant->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Tomato', 'health_status' => 'Diseased']);

        $this->assertDatabaseHas('plants', [
            'id' => $plant->id,
            'name' => 'Updated Tomato',
        ]);
    }

    public function test_destroy_deletes_plant(): void
    {
        $plant = Plant::factory()->create(['farm_id' => $this->farm->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/farms/{$this->farm->id}/plants/{$plant->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Plant deleted successfully']);

        $this->assertDatabaseMissing('plants', ['id' => $plant->id]);
    }

    public function test_destroy_returns_not_found_for_plant_not_in_farm(): void
    {
        $otherUser = User::factory()->create();
        $otherFarm = Farm::factory()->create(['user_id' => $otherUser->id]);
        $plant = Plant::factory()->create(['farm_id' => $otherFarm->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/farms/{$this->farm->id}/plants/{$plant->id}");

        $response->assertStatus(404);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson("/api/farms/{$this->farm->id}/plants");

        $response->assertStatus(401);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson("/api/farms/{$this->farm->id}/plants", []);

        $response->assertStatus(401);
    }
}
