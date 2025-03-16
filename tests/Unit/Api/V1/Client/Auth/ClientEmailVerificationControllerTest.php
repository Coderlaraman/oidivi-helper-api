<?php

namespace Tests\Unit\Api\V1\Client\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ClientEmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_unverified_user_can_request_a_verification_email()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->postJson('/api/v1/client/auth/email/verification-notification');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verification email sent.'
            ]);
    }

    #[Test]
    public function a_verified_user_cannot_request_another_verification_email()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson('/api/v1/client/auth/email/verification-notification');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email already verified.'
            ]);
    }

    #[Test]
    public function a_user_can_verify_email_with_a_valid_hash()
    {
        Event::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        $hash = sha1($user->getEmailForVerification());

        $response = $this->getJson("/api/v1/client/auth/email/verify/{$user->id}/{$hash}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email successfully verified.'
            ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
        Event::assertDispatched(Verified::class);
    }

    #[Test]
    public function a_user_cannot_verify_email_with_an_invalid_hash()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $invalidHash = sha1('invalid@example.com');

        $response = $this->getJson("/api/v1/client/auth/email/verify/{$user->id}/{$invalidHash}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid verification hash.'
            ]);
    }

    #[Test]
    public function a_user_who_is_already_verified_cannot_verify_again()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $hash = sha1($user->getEmailForVerification());

        $response = $this->getJson("/api/v1/client/auth/email/verify/{$user->id}/{$hash}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email already verified.'
            ]);
    }
}
