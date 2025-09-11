<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContractAcceptConnectGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure roles exist if using roles table
        \DB::table('roles')->insertOrIgnore([
            ['name' => 'helper'],
            ['name' => 'client'],
        ]);
    }

    private function createClientAndHelper(): array
    {
        $client = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $client->assignRole('client');

        $helper = User::factory()->create([
            'password' => Hash::make('password'),
            'stripe_account_id' => 'acct_123',
            'stripe_charges_enabled' => false,
            'stripe_payouts_enabled' => false,
        ]);
        $helper->assignRole('helper');

        return [$client, $helper];
    }

    private function createSentContract(User $client, User $helper): Contract
    {
        $serviceRequest = ServiceRequest::factory()->create(['user_id' => $client->id]);
        $serviceOffer = ServiceOffer::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $helper->id,
            'status' => ServiceOffer::STATUS_PENDING,
        ]);

        $contract = Contract::create([
            'service_request_id' => $serviceRequest->id,
            'service_offer_id' => $serviceOffer->id,
            'client_id' => $client->id,
            'provider_id' => $helper->id,
            'status' => Contract::STATUS_DRAFT,
        ]);

        $contract->markAsSent();

        return $contract;
    }

    public function test_accept_is_gated_without_connect_enabled(): void
    {
        [$client, $helper] = $this->createClientAndHelper();
        $contract = $this->createSentContract($client, $helper);

        $response = $this->actingAs($helper, 'sanctum')->postJson("/api/v1/user/contracts/{$contract->id}/accept");

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'onboarding_url',
                    'charges_enabled',
                    'payouts_enabled',
                ]
            ]);
    }

    public function test_accept_succeeds_when_connect_is_enabled(): void
    {
        [$client, $helper] = $this->createClientAndHelper();
        $helper->update([
            'stripe_charges_enabled' => true,
            'stripe_payouts_enabled' => true,
        ]);
        $contract = $this->createSentContract($client, $helper);

        $response = $this->actingAs($helper, 'sanctum')->postJson("/api/v1/user/contracts/{$contract->id}/accept");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', Contract::STATUS_ACCEPTED);
    }
}