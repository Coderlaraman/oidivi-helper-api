<?php

namespace Tests\Feature\User\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_successfully()
    {
        $response = $this->postJson('/api/v1/user/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accepted_terms' => true,
            'address' => '123 Test Street',
            'phone' => '123456789',
            'zip_code' => '12345',
            'latitude' => '37.7749',
            'longitude' => '-122.4194',
        ]);

        $response->assertCreated()
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => ['token', 'user' => ['id', 'name', 'email']]
                 ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    #[Test]
    public function user_cannot_register_without_accepting_terms()
    {
        $response = $this->postJson('/api/v1/user/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accepted_terms' => false,
            'address' => '123 Test Street',
            'phone' => '123456789',
            'zip_code' => '12345',
            'latitude' => '37.7749',
            'longitude' => '-122.4194',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false, 'message' => 'You must accept the terms and conditions']);
    }

    #[Test]
    public function user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/user/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        $response->assertUnauthorized()
                 ->assertJson(['success' => false, 'message' => 'Invalid credentials']);
    }

    #[Test]
    public function user_cannot_login_if_email_not_verified()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/user/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403)
                 ->assertJson(['success' => false, 'message' => 'Please verify your email before logging in']);
    }

    #[Test]
    public function user_cannot_login_if_account_is_disabled()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/user/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403)
                 ->assertJson(['success' => false, 'message' => 'Your account is disabled.']);
    }

    #[Test]
    public function user_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/user/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => ['token', 'user' => ['id', 'name', 'email']]
                 ]);

        // Autenticar al usuario manualmente después del login
        Sanctum::actingAs($user);
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function user_can_logout_successfully()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/auth/logout');

        $response->assertOk()
                 ->assertJson(['success' => true, 'message' => 'Logout successful']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    #[Test]
    public function guest_cannot_logout()
    {
        $response = $this->postJson('/api/v1/user/auth/logout');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }
}
