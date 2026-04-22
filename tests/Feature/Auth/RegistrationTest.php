<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can register without engineer_id.
     */
    public function test_user_can_register_without_engineer_id(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'handle' => 'john_doe',
            'phone' => '1234567890',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'farmer',
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'email' => 'john@example.com',
                        'handle' => 'john_doe',
                        'role' => 'farmer',
                    ],
                ],
            ]);

        // Verify user was created without engineer_id
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'handle' => 'john_doe',
            'engineer_id' => null,
        ]);
    }

    /**
     * Test engineer_id field is ignored if sent during registration.
     */
    public function test_engineer_id_is_ignored_during_registration(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'handle' => 'jane_smith',
            'phone' => '0987654321',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'farmer',
            'engineer_id' => 999, // This should be ignored
        ]);

        $response->assertOk();

        // Verify engineer_id was not set
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'handle' => 'jane_smith',
            'engineer_id' => null,
        ]);
    }

    /**
     * Test registration requires all fields.
     */
    public function test_registration_requires_all_fields(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'John',
            // Missing other required fields
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'last_name',
                'email',
                'handle',
                'phone',
                'password',
                'role',
            ]);
    }

    /**
     * Test registration rejects invalid email.
     */
    public function test_registration_rejects_invalid_email(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'handle' => 'john_doe',
            'phone' => '1234567890',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'farmer',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test registration rejects duplicate email.
     */
    public function test_registration_rejects_duplicate_email(): void
    {
        $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'handle' => 'john_doe',
            'phone' => '1234567890',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'farmer',
        ]);

        // Log out to test as a guest
        Auth::logout();
        $response = $this->postJson('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'handle' => 'jane_smith',
            'phone' => '0987654321',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'farmer',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test password confirmation required.
     */
    public function test_password_confirmation_required(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'handle' => 'john_doe',
            'phone' => '1234567890',
            'password' => 'Password@123',
            'password_confirmation' => 'WrongPassword@123',
            'role' => 'farmer',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test engineer role can be registered.
     */
    public function test_engineer_role_can_be_registered(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'Engineer',
            'last_name' => 'User',
            'email' => 'engineer@example.com',
            'handle' => 'engineer_user',
            'phone' => '1111111111',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'engineer',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'engineer@example.com',
            'handle' => 'engineer_user',
            'role' => 'engineer',
            'engineer_id' => null,
        ]);
    }

    /**
     * Test handle is required for registration.
     */
    public function test_handle_is_required(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            // Missing handle
            'phone' => '1234567890',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'farmer',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['handle']);
    }

    /**
     * Test handle must be unique.
     */
    public function test_handle_must_be_unique(): void
    {
        $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'handle' => 'john_doe',
            'phone' => '1234567890',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'farmer',
        ]);

        Auth::logout();

        $response = $this->postJson('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'handle' => 'john_doe', // Same handle
            'phone' => '0987654321',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => 'farmer',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['handle']);
    }

    /**
     * Test handle must follow slug format (lowercase, no spaces, only underscores/dots).
     */
    public function test_handle_must_follow_slug_format(): void
    {
        $invalidHandles = ['JohnDoe', 'john doe', 'john-doe', 'john@doe', 'john#doe'];

        foreach ($invalidHandles as $handle) {
            Auth::logout();

            $response = $this->postJson('/register', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john'.uniqid().'@example.com',
                'handle' => $handle,
                'phone' => '1234567890',
                'password' => 'Password@123',
                'password_confirmation' => 'Password@123',
                'role' => 'farmer',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['handle']);
        }
    }

    /**
     * Test valid handle formats are accepted.
     */
    public function test_valid_handle_formats_are_accepted(): void
    {
        $validHandles = ['john_doe', 'jane.smith', 'engineer_1', 'user_123_test', 'simple'];

        foreach ($validHandles as $handle) {
            Auth::logout();

            $response = $this->postJson('/register', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john'.uniqid().'@example.com',
                'handle' => $handle,
                'phone' => '1234567890',
                'password' => 'Password@123',
                'password_confirmation' => 'Password@123',
                'role' => 'farmer',
            ]);

            $response->assertOk();

            $this->assertDatabaseHas('users', [
                'handle' => $handle,
            ]);
        }
    }
}
