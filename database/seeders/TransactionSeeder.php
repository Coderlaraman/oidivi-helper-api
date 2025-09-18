<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $serviceRequests = ServiceRequest::all();

        if ($users->isEmpty()) {
            $this->command->error('Please run UserSeeder first');
            return;
        }

        $this->command->info('Creating transactions...');

        // Crear transacciones para cada usuario
        $users->each(function ($user) use ($serviceRequests) {
            // Transacciones como cliente (pagos)
            $this->createClientTransactions($user, $serviceRequests);
            
            // Transacciones como helper (ingresos)
            $this->createHelperTransactions($user, $serviceRequests);
            
            // Transacciones del sistema (comisiones, bonos, etc.)
            $this->createSystemTransactions($user);
        });

        // Crear algunas transacciones específicas de ejemplo
        $this->createExampleTransactions($users, $serviceRequests);

        $this->command->info('Transactions created successfully!');
    }

    /**
     * Crear transacciones como cliente
     */
    private function createClientTransactions(User $user, $serviceRequests): void
    {
        // Pagos por servicios solicitados
        $userServiceRequests = $serviceRequests->where('user_id', $user->id)->take(rand(1, 3));
        
        foreach ($userServiceRequests as $serviceRequest) {
            // Pago principal
            Transaction::factory()->payment()->completed()->create([
                'user_id' => $user->id,
                'related_model_type' => 'App\\Models\\ServiceRequest',
                'related_model_id' => $serviceRequest->id,
                'amount' => $serviceRequest->budget,
                'description' => "Payment for service: {$serviceRequest->title}",
            ]);

            // Posible reembolso (20% de probabilidad)
            if (rand(1, 100) <= 20) {
                Transaction::factory()->refund()->completed()->create([
                    'user_id' => $user->id,
                    'related_model_type' => 'App\\Models\\ServiceRequest',
                    'related_model_id' => $serviceRequest->id,
                    'amount' => $serviceRequest->budget * 0.5, // Reembolso parcial
                    'description' => "Partial refund for service: {$serviceRequest->title}",
                ]);
            }
        }
    }

    /**
     * Crear transacciones como helper
     */
    private function createHelperTransactions(User $user, $serviceRequests): void
    {
        // Ingresos por servicios completados
        $completedServices = $serviceRequests->where('status', 'completed');
        
        if ($completedServices->isEmpty()) {
            // Si no hay servicios completados, tomar algunos aleatorios y simular que están completados
            $completedServices = $serviceRequests->random(min(rand(1, 4), $serviceRequests->count()));
        } else {
            $completedServices = $completedServices->random(min(rand(1, 4), $completedServices->count()));
        }
        
        foreach ($completedServices as $serviceRequest) {
            $serviceAmount = $serviceRequest->budget;
            $commissionRate = 0.15; // 15% de comisión
            $commissionAmount = $serviceAmount * $commissionRate;
            $netAmount = $serviceAmount - $commissionAmount;

            // Pago recibido por el servicio
            Transaction::factory()->create([
                'user_id' => $user->id,
                'related_model_type' => 'App\\Models\\ServiceRequest',
                'related_model_id' => $serviceRequest->id,
                'type' => 'deposit',
                'amount' => $netAmount,
                'status' => 'completed',
                'description' => "Payment received for service: {$serviceRequest->title}",
                'processed_at' => now()->subDays(rand(1, 30)),
            ]);

            // Comisión de la plataforma
            Transaction::factory()->commission()->completed()->create([
                'user_id' => $user->id,
                'related_model_type' => 'App\\Models\\ServiceRequest',
                'related_model_id' => $serviceRequest->id,
                'amount' => $commissionAmount,
                'description' => "Platform commission for service: {$serviceRequest->title}",
            ]);
        }
    }

    /**
     * Crear transacciones del sistema
     */
    private function createSystemTransactions(User $user): void
    {
        // Bono de bienvenida (solo para algunos usuarios)
        if (rand(1, 100) <= 30) {
            Transaction::factory()->create([
                'user_id' => $user->id,
                'type' => 'bonus',
                'amount' => 25.00,
                'status' => 'completed',
                'description' => 'Welcome bonus for new user',
                'processed_at' => $user->created_at->addHours(1),
            ]);
        }

        // Retiros (para usuarios con saldo)
        if (rand(1, 100) <= 40) {
            Transaction::factory()->create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => rand(50, 300),
                'status' => fake()->randomElement(['completed', 'pending', 'processing']),
                'description' => 'Withdrawal to bank account ending in ' . rand(1000, 9999),
                'processed_at' => fake()->boolean(70) ? now()->subDays(rand(1, 15)) : null,
            ]);
        }

        // Tarifas de procesamiento
        if (rand(1, 100) <= 25) {
            Transaction::factory()->create([
                'user_id' => $user->id,
                'type' => 'fee',
                'amount' => fake()->randomFloat(2, 1.50, 8.00),
                'status' => 'completed',
                'description' => 'Processing fee for withdrawal',
                'processed_at' => now()->subDays(rand(1, 20)),
            ]);
        }

        // Penalizaciones (raras)
        if (rand(1, 100) <= 10) {
            Transaction::factory()->create([
                'user_id' => $user->id,
                'type' => 'penalty',
                'amount' => fake()->randomFloat(2, 10.00, 50.00),
                'status' => 'completed',
                'description' => 'Penalty for late service cancellation',
                'processed_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    /**
     * Crear transacciones de ejemplo específicas
     */
    private function createExampleTransactions($users, $serviceRequests): void
    {
        $adminUser = $users->first(); // Asumir que el primer usuario es admin
        
        if (!$adminUser) return;

        // Transacciones de diferentes estados para testing
        $exampleTransactions = [
            [
                'type' => 'payment',
                'amount' => 150.00,
                'status' => 'pending',
                'description' => 'Pending payment for urgent plumbing repair',
            ],
            [
                'type' => 'payment',
                'amount' => 75.50,
                'status' => 'failed',
                'description' => 'Failed payment - insufficient funds',
            ],
            [
                'type' => 'refund',
                'amount' => 120.00,
                'status' => 'processing',
                'description' => 'Refund in progress for cancelled cleaning service',
            ],
            [
                'type' => 'withdrawal',
                'amount' => 500.00,
                'status' => 'completed',
                'description' => 'Monthly earnings withdrawal',
            ],
            [
                'type' => 'deposit',
                'amount' => 200.00,
                'status' => 'completed',
                'description' => 'Account top-up via credit card',
            ],
        ];

        foreach ($exampleTransactions as $transactionData) {
            $randomServiceRequest = $serviceRequests->random();
            Transaction::factory()->create(array_merge([
                'user_id' => $adminUser->id,
                'related_model_type' => $randomServiceRequest ? 'App\\Models\\ServiceRequest' : null,
                'related_model_id' => $randomServiceRequest ? $randomServiceRequest->id : null,
                'processed_at' => $transactionData['status'] === 'completed' ? 
                    now()->subDays(rand(1, 7)) : null,
            ], $transactionData));
        }

        // Crear un conjunto de transacciones para mostrar tendencias
        $this->createTrendTransactions($adminUser);
    }

    /**
     * Crear transacciones para mostrar tendencias mensuales
     */
    private function createTrendTransactions(User $user): void
    {
        // Crear transacciones de los últimos 6 meses
        for ($month = 5; $month >= 0; $month--) {
            $date = now()->subMonths($month);
            $transactionCount = rand(3, 8);
            
            for ($i = 0; $i < $transactionCount; $i++) {
                $type = fake()->randomElement(['payment', 'deposit', 'commission', 'withdrawal']);
                
                Transaction::factory()->create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'status' => 'completed',
                    'processed_at' => $date->copy()->addDays(rand(1, 28)),
                    'created_at' => $date->copy()->addDays(rand(1, 28)),
                    'updated_at' => $date->copy()->addDays(rand(1, 28)),
                ]);
            }
        }
    }
}