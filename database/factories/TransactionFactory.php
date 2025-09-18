<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['payment', 'refund', 'commission', 'withdrawal', 'deposit', 'fee', 'bonus', 'penalty'];
        $statuses = ['pending', 'completed', 'failed', 'cancelled', 'processing', 'refunded'];
        $paymentMethods = ['credit_card', 'bank_transfer', 'paypal', 'stripe', 'cash'];
        
        $type = $this->faker->randomElement($types);
        $amount = $this->generateAmountByType($type);
        
        $hasServiceRequest = $this->faker->boolean(80);
        
        return [
            'user_id' => User::factory(),
            'related_model_type' => $hasServiceRequest ? 'App\\Models\\ServiceRequest' : null,
            'related_model_id' => $hasServiceRequest ? ServiceRequest::factory() : null,
            'type' => $type,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => $this->faker->randomElement($statuses),
            'description' => $this->generateDescriptionByType($type),
            'reference' => $this->generateReference(),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'payment_provider_id' => $this->faker->uuid(),
            'fee_amount' => $this->faker->randomFloat(2, 0.50, 15.00),
            'net_amount' => $amount - $this->faker->randomFloat(2, 0.50, 15.00),
            'metadata' => $this->generateMetadata($type),
            'processed_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
        ];
    }

    /**
     * Generate amount based on transaction type
     */
    private function generateAmountByType(string $type): float
    {
        return match($type) {
            'payment' => $this->faker->randomFloat(2, 25.00, 500.00),
            'refund' => $this->faker->randomFloat(2, 10.00, 300.00),
            'commission' => $this->faker->randomFloat(2, 2.50, 50.00),
            'withdrawal' => $this->faker->randomFloat(2, 50.00, 1000.00),
            'deposit' => $this->faker->randomFloat(2, 20.00, 800.00),
            'fee' => $this->faker->randomFloat(2, 1.00, 25.00),
            'bonus' => $this->faker->randomFloat(2, 5.00, 100.00),
            'penalty' => $this->faker->randomFloat(2, 5.00, 75.00),
            default => $this->faker->randomFloat(2, 10.00, 200.00)
        };
    }

    /**
     * Generate description based on transaction type
     */
    private function generateDescriptionByType(string $type): string
    {
        return match($type) {
            'payment' => 'Payment for service: ' . $this->faker->words(3, true),
            'refund' => 'Refund for cancelled service: ' . $this->faker->words(2, true),
            'commission' => 'Platform commission for completed service',
            'withdrawal' => 'Withdrawal to bank account ending in ' . $this->faker->numberBetween(1000, 9999),
            'deposit' => 'Deposit from ' . $this->faker->randomElement(['bank transfer', 'credit card', 'paypal']),
            'fee' => 'Processing fee for ' . $this->faker->randomElement(['payment', 'withdrawal', 'transfer']),
            'bonus' => 'Bonus for ' . $this->faker->randomElement(['referral', 'first service', 'loyalty program']),
            'penalty' => 'Penalty for ' . $this->faker->randomElement(['late cancellation', 'policy violation', 'chargeback']),
            default => 'Transaction: ' . $this->faker->words(2, true)
        };
    }

    /**
     * Generate transaction reference
     */
    private function generateReference(): string
    {
        return 'TXN-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd');
    }

    /**
     * Generate metadata based on transaction type
     */
    private function generateMetadata(string $type): array
    {
        $baseMetadata = [
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_by' => 'system'
        ];

        return match($type) {
            'payment' => array_merge($baseMetadata, [
                'service_category' => $this->faker->randomElement(['cleaning', 'plumbing', 'electrical', 'gardening']),
                'payment_processor' => $this->faker->randomElement(['stripe', 'paypal']),
            ]),
            'refund' => array_merge($baseMetadata, [
                'refund_reason' => $this->faker->randomElement(['service_cancelled', 'customer_request', 'quality_issue']),
                'original_transaction_id' => 'TXN-' . strtoupper(Str::random(8)),
            ]),
            'withdrawal' => array_merge($baseMetadata, [
                'bank_name' => $this->faker->company(),
                'account_last_four' => $this->faker->numberBetween(1000, 9999),
            ]),
            default => $baseMetadata
        };
    }

    /**
     * Create a completed payment transaction
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Create a pending transaction
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
        ]);
    }

    /**
     * Create a payment type transaction
     */
    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payment',
            'amount' => $this->faker->randomFloat(2, 25.00, 500.00),
            'description' => 'Payment for service: ' . $this->faker->words(3, true),
        ]);
    }

    /**
     * Create a commission type transaction
     */
    public function commission(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'commission',
            'amount' => $this->faker->randomFloat(2, 2.50, 50.00),
            'description' => 'Platform commission for completed service',
        ]);
    }

    /**
     * Create a refund type transaction
     */
    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'refund',
            'amount' => $this->faker->randomFloat(2, 10.00, 300.00),
            'description' => 'Refund for cancelled service: ' . $this->faker->words(2, true),
        ]);
    }
}