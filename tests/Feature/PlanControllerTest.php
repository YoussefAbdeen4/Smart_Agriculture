<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanControllerTest extends TestCase
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

    public function test_index_returns_all_plans_for_farm(): void
    {
        Plan::factory(3)->create(['farm_id' => $this->farm->id]);

        $response = $this->actingAs($this->user)->getJson("/api/farms/{$this->farm->id}/plans");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'plans' => [
                        '*' => [
                            'id',
                            'name',
                            'irrigation_date',
                            'fertilization_date',
                            'farm_id',
                            'plants',
                        ],
                    ],
                ],
            ]);
    }

    public function test_store_creates_new_plan(): void
    {
        $data = [
            'name' => 'Spring Plan',
            'irrigation_date' => '2026-04-25',
            'fertilization_date' => '2026-04-30',
            'note' => 'Spring planting schedule',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/farms/{$this->farm->id}/plans", $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'plan' => [
                        'id',
                        'name',
                        'irrigation_date',
                        'fertilization_date',
                        'farm_id',
                        'plants',
                    ],
                ],
            ])
            ->assertJsonFragment(['note' => 'Spring planting schedule']);

        $this->assertDatabaseHas('plan', [
            'farm_id' => $this->farm->id,
            'name' => 'Spring Plan',
            'note' => 'Spring planting schedule',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/farms/{$this->farm->id}/plans", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'irrigation_date', 'fertilization_date']);
    }

    public function test_store_validates_date_format(): void
    {
        $data = [
            'irrigation_date' => 'invalid-date',
            'fertilization_date' => '2026-04-30',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/farms/{$this->farm->id}/plans", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['irrigation_date']);
    }

    public function test_show_returns_specific_plan(): void
    {
        $plan = Plan::factory()->create(['farm_id' => $this->farm->id]);

        $response = $this->actingAs($this->user)->getJson("/api/farms/{$this->farm->id}/plans/{$plan->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'plan' => [
                        'id',
                        'name',
                        'irrigation_date',
                        'fertilization_date',
                        'plants',
                    ],
                ],
            ]);
    }

    public function test_show_returns_not_found_for_plan_not_in_farm(): void
    {
        $otherUser = User::factory()->create();
        $otherFarm = Farm::factory()->create(['user_id' => $otherUser->id]);
        $plan = Plan::factory()->create(['farm_id' => $otherFarm->id]);

        $response = $this->actingAs($this->user)->getJson("/api/farms/{$this->farm->id}/plans/{$plan->id}");

        $response->assertStatus(404);
    }

    public function test_update_modifies_plan(): void
    {
        $plan = Plan::factory()->create(['farm_id' => $this->farm->id]);

        $data = [
            'irrigation_date' => '2026-05-01',
            'note' => 'Updated schedule',
        ];

        $response = $this->actingAs($this->user)->patchJson("/api/farms/{$this->farm->id}/plans/{$plan->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['note' => 'Updated schedule']);

        $this->assertDatabaseHas('plan', [
            'id' => $plan->id,
            'note' => 'Updated schedule',
        ]);
    }

    public function test_destroy_deletes_plan(): void
    {
        $plan = Plan::factory()->create(['farm_id' => $this->farm->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/farms/{$this->farm->id}/plans/{$plan->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Plan deleted successfully']);

        $this->assertDatabaseMissing('plan', ['id' => $plan->id]);
    }

    public function test_destroy_returns_not_found_for_plan_not_in_farm(): void
    {
        $otherUser = User::factory()->create();
        $otherFarm = Farm::factory()->create(['user_id' => $otherUser->id]);
        $plan = Plan::factory()->create(['farm_id' => $otherFarm->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/farms/{$this->farm->id}/plans/{$plan->id}");

        $response->assertStatus(404);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson("/api/farms/{$this->farm->id}/plans");

        $response->assertStatus(401);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson("/api/farms/{$this->farm->id}/plans", []);

        $response->assertStatus(401);
    }
}
