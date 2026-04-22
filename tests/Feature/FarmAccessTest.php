<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test farm owner can grant access to any user.
     */
    public function test_farm_owner_can_grant_access_to_any_user(): void
    {
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);
        $userToAdd = User::factory()->create(['role' => 'farmer']);

        $response = $this->actingAs($owner)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => $userToAdd->handle,
            'role' => 'viewer',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Access granted successfully',
            ]);

        $this->assertTrue($farm->users()->where('user_id', $userToAdd->id)->exists());
    }

    /**
     * Test engineer with editor access can grant access to their supervised farmers.
     */
    public function test_engineer_with_editor_access_can_grant_access_to_supervised_farmers(): void
    {
        $engineer = User::factory()->create(['role' => 'engineer']);
        $farmer = User::factory()->create(['role' => 'farmer', 'engineer_id' => null]);
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);

        // Grant editor access to the engineer first
        $farm->users()->attach($engineer->id, ['role' => 'editor']);

        $response = $this->actingAs($engineer)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => $farmer->handle,
            'role' => 'viewer',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Access granted successfully',
            ]);

        $this->assertTrue($farm->users()->where('user_id', $farmer->id)->exists());

        // Verify engineer_id was updated
        $this->assertEquals($engineer->id, $farmer->fresh()->engineer_id);
    }

    /**
     * Test engineer cannot grant access to farmers not under their supervision.
     */
    public function test_engineer_cannot_grant_access_to_farmers_not_under_supervision(): void
    {
        $engineer1 = User::factory()->create(['role' => 'engineer']);
        $engineer2 = User::factory()->create(['role' => 'engineer']);
        $farmer = User::factory()->create(['role' => 'farmer', 'engineer_id' => $engineer2->id]);
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);

        // Grant editor access to engineer1
        $farm->users()->attach($engineer1->id, ['role' => 'editor']);

        $response = $this->actingAs($engineer1)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => $farmer->handle,
            'role' => 'viewer',
        ]);

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Unauthorized',
            ]);

        $this->assertFalse($farm->users()->where('user_id', $farmer->id)->exists());
    }

    /**
     * Test engineer_id is automatically set when engineer grants access to unassigned farmer.
     */
    public function test_engineer_id_automatically_set_when_granting_access_to_unassigned_farmer(): void
    {
        $engineer = User::factory()->create(['role' => 'engineer']);
        $farmer = User::factory()->create(['role' => 'farmer', 'engineer_id' => null]);
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);

        // Verify farmer has no engineer
        $this->assertNull($farmer->engineer_id);

        // Grant editor access to the engineer first
        $farm->users()->attach($engineer->id, ['role' => 'editor']);

        // Grant access to the farmer
        $this->actingAs($engineer)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => $farmer->handle,
            'role' => 'viewer',
        ]);

        // Verify engineer_id was automatically set
        $this->assertEquals($engineer->id, $farmer->fresh()->engineer_id);
    }

    /**
     * Test owner can grant access without updating engineer_id.
     */
    public function test_owner_can_grant_access_without_modifying_engineer_id(): void
    {
        $owner = User::factory()->create(['role' => 'farmer']);
        $engineer1 = User::factory()->create(['role' => 'engineer']);
        $farmer = User::factory()->create(['role' => 'farmer', 'engineer_id' => $engineer1->id]);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => $farmer->handle,
            'role' => 'viewer',
        ]);

        $response->assertOk();

        // Verify engineer_id was NOT modified (owner can't change it)
        $this->assertEquals($engineer1->id, $farmer->fresh()->engineer_id);

        // But access was granted
        $this->assertTrue($farm->users()->where('user_id', $farmer->id)->exists());
    }

    /**
     * Test engineer with viewer access cannot grant access.
     */
    public function test_engineer_with_viewer_access_cannot_grant_access(): void
    {
        $engineer = User::factory()->create(['role' => 'engineer']);
        $farmer = User::factory()->create(['role' => 'farmer', 'engineer_id' => $engineer->id]);
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);

        // Grant only viewer access to the engineer
        $farm->users()->attach($engineer->id, ['role' => 'viewer']);

        $response = $this->actingAs($engineer)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => $farmer->handle,
            'role' => 'viewer',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test farm owner can revoke access.
     */
    public function test_farm_owner_can_revoke_access(): void
    {
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);
        $user = User::factory()->create();

        $farm->users()->attach($user->id, ['role' => 'viewer']);

        $response = $this->actingAs($owner)->postJson("/api/farms/{$farm->id}/revoke-access", [
            'user_id' => $user->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Access revoked successfully',
            ]);

        $this->assertFalse($farm->users()->where('user_id', $user->id)->exists());
    }

    /**
     * Test farm owner can view access list.
     */
    public function test_farm_owner_can_view_access_list(): void
    {
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $farm->users()->attach($user1->id, ['role' => 'editor']);
        $farm->users()->attach($user2->id, ['role' => 'viewer']);

        $response = $this->actingAs($owner)->getJson("/api/farms/{$farm->id}/access-list");

        $response->assertOk()
            ->assertJson([
                'message' => 'Access list retrieved successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'users' => [
                        '*' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                        ],
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('data.users'));
    }

    /**
     * Test user with access can revoke their own access.
     */
    public function test_user_with_access_can_view_farm(): void
    {
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);
        $editor = User::factory()->create();

        $farm->users()->attach($editor->id, ['role' => 'editor']);

        $response = $this->actingAs($editor)->getJson("/api/farms/{$farm->id}");

        $response->assertOk();
    }

    /**
     * Test unauthorized user cannot grant access.
     */
    public function test_unauthorized_user_cannot_grant_access(): void
    {
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);
        $unauthorized = User::factory()->create(['role' => 'farmer']);
        $userToAdd = User::factory()->create();

        $response = $this->actingAs($unauthorized)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => $userToAdd->handle,
            'role' => 'viewer',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test grant access validation for non-existent user.
     */
    public function test_grant_access_validation_for_non_existent_user(): void
    {
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => 'nonexistent_user',
            'role' => 'viewer',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['handle']);
    }

    /**
     * Test grant access with invalid role.
     */
    public function test_grant_access_with_invalid_role(): void
    {
        $owner = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::factory()->create(['user_id' => $owner->id]);
        $user = User::factory()->create();

        $response = $this->actingAs($owner)->postJson("/api/farms/{$farm->id}/grant-access", [
            'handle' => $user->handle,
            'role' => 'admin',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    /**
     * Test staff list route returns only engineers' staff.
     */
    public function test_staff_list_returns_only_supervised_farmers(): void
    {
        $engineer = User::factory()->create(['role' => 'engineer']);
        $farmer1 = User::factory()->create(['role' => 'farmer', 'engineer_id' => $engineer->id]);
        $farmer2 = User::factory()->create(['role' => 'farmer', 'engineer_id' => $engineer->id]);
        $otherFarmer = User::factory()->create(['role' => 'farmer']);

        $response = $this->actingAs($engineer)->getJson('/api/staff');

        $response->assertOk()
            ->assertJson([
                'message' => 'Staff retrieved successfully',
            ]);

        $staff = $response->json('data.staff');
        $this->assertCount(2, $staff);
        $this->assertEquals($farmer1->id, $staff[0]['id']);
        $this->assertEquals($farmer2->id, $staff[1]['id']);
    }

    /**
     * Test non-engineer cannot access staff list.
     */
    public function test_non_engineer_cannot_access_staff_list(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);

        $response = $this->actingAs($farmer)->getJson('/api/staff');

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }
}
