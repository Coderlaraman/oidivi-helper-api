<?php

namespace Tests\Unit\Api\V1\Client\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ClientAuthControllerTest extends TestCase
{
    use RefreshDatabase;


    #[Test] //+
    public function a_client_can_register_successfully()
    {
        $response = $this->postJson('/api/v1/client/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accepted_terms' => true,
            'address' => '123 Main St',
            'phone' => '1234567890',
            'zip_code' => '12345',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user']
            ]);
    }

    #[Test]
    public function a_client_cannot_register_without_accepting_terms()
    {
        $response = $this->postJson('/api/v1/client/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accepted_terms' => false,
            'address' => '123 Main St',
            'phone' => '1234567890',
            'zip_code' => '12345',
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function a_client_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/client/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user']
            ]);
    }

    #[Test]
    public function a_client_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/client/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function an_authenticated_client_can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/client/auth/logout');

        $response->assertStatus(200);
    }
}
