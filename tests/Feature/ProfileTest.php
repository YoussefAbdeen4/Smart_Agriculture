<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting authenticated user's profile.
     */
    public function test_authenticated_user_can_view_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertOk()
            ->assertJson([
                'message' => 'Profile retrieved successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'handle',
                        'phone',
                        'role',
                    ],
                ],
            ]);

        $this->assertEquals($user->id, $response->json('data.user.id'));
    }

    /**
     * Test unauthenticated user cannot view profile.
     */
    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertUnauthorized();
    }

    /**
     * Test updating user profile with valid data.
     */
    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '0987654321',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'first_name' => 'Jane',
                        'last_name' => 'Smith',
                        'phone' => '0987654321',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '0987654321',
        ]);
    }

    /**
     * Test updating email with unique validation.
     */
    public function test_cannot_update_profile_with_duplicate_email(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($user2)->putJson('/api/profile', [
            'email' => 'user1@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test updating email to a unique value.
     */
    public function test_can_update_profile_with_unique_email(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'email' => 'new@example.com',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);
    }

    /**
     * Test validation of phone number format.
     */
    public function test_phone_number_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'phone' => 'invalid phone with special chars @#$%',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    /**
     * Test valid phone number formats.
     */
    public function test_valid_phone_numbers(): void
    {
        $user = User::factory()->create();

        $validPhones = [
            '1234567890',
            '+1-234-567-8900',
            '(123) 456-7890',
            '+1 234 567 8900',
        ];

        foreach ($validPhones as $phone) {
            $response = $this->actingAs($user)->putJson('/api/profile', [
                'phone' => $phone,
            ]);

            $response->assertOk();
        }
    }

    /**
     * Test deleting user account.
     */
    public function test_authenticated_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $response = $this->actingAs($user)->deleteJson('/api/profile');

        $response->assertOk()
            ->assertJson([
                'message' => 'Account deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    /**
     * Test deleting user revokes all tokens.
     */
    public function test_deleting_account_revokes_all_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('test-token');

        $this->actingAs($user)->deleteJson('/api/profile');

        // Verify user is deleted
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        // Verify tokens are deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * Test getting user's farms.
     */
    public function test_authenticated_user_can_view_their_farms(): void
    {
        $user = User::factory()->create();
        $farms = Farm::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/profile/farms');

        $response->assertOk()
            ->assertJson([
                'message' => 'Farms retrieved successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'farms' => [
                        '*' => [
                            'id',
                            'name',
                            'location',
                            'area',
                            'soil_type',
                            'plants_count',
                            'owner',
                            'assigned_engineers',
                        ],
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data.farms'));
    }

    /**
     * Test farms list includes farm data in table format.
     */
    public function test_farms_returned_in_table_format(): void
    {
        $user = User::factory()->create();
        $farm = Farm::factory()->create([
            'user_id' => $user->id,
            'name' => 'Green Valley Farm',
            'location' => 'North Region',
            'soil_type' => 'Loamy',
        ]);

        $response = $this->actingAs($user)->getJson('/api/profile/farms');

        $response->assertOk();

        $farmData = $response->json('data.farms.0');
        $this->assertEquals($farm->id, $farmData['id']);
        $this->assertEquals('Green Valley Farm', $farmData['name']);
        $this->assertEquals('North Region', $farmData['location']);
        $this->assertEquals('Loamy', $farmData['soil_type']);
        $this->assertIsNumeric($farmData['plants_count']);
        $this->assertArrayHasKey('owner', $farmData);
        $this->assertArrayHasKey('assigned_engineers', $farmData);
    }

    /**
     * Test unauthenticated user cannot view farms.
     */
    public function test_unauthenticated_user_cannot_view_farms(): void
    {
        $response = $this->getJson('/api/profile/farms');

        $response->assertUnauthorized();
    }

    /**
     * Test validation of image upload.
     */
    public function test_image_upload_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'img' => 'not-an-image',
        ]);

        $response->assertUnprocessable();
    }

    /**
     * Test max field lengths.
     */
    public function test_max_length_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'first_name' => str_repeat('a', 256),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name']);
    }

    /**
     * Test handle cannot be updated after registration.
     */
    public function test_handle_cannot_be_updated(): void
    {
        $user = User::factory()->create(['handle' => 'original_handle']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'handle' => 'new_handle',
            'first_name' => 'Updated',
        ]);

        $response->assertOk();

        // Verify handle was NOT updated
        $this->assertEquals('original_handle', $user->fresh()->handle);

        // Verify first_name WAS updated
        $this->assertEquals('Updated', $user->fresh()->first_name);
    }

    /**
     * Test handle is included in profile response.
     */
    public function test_handle_is_included_in_profile_response(): void
    {
        $user = User::factory()->create(['handle' => 'test_handle']);

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'handle' => 'test_handle',
                    ],
                ],
            ]);
    }
}
