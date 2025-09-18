<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Relaciones con entidades del sistema
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('payer_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payee_user_id')->constrained('users')->onDelete('cascade');
            
            // Campos financieros
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('fee_amount', 10, 2)->default(0); // Comisiones aplicadas
            $table->decimal('net_amount', 15, 2)->nullable(); // Monto neto después de comisiones
            
            // Estado del pago
            $table->enum('status', [
                'pending',
                'processing', 
                'completed',
                'failed',
                'cancelled',
                'refunded',
                'partially_refunded'
            ])->default('pending');
            
            // Información de Stripe
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->json('stripe_metadata')->nullable();
            
            // Fechas importantes
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            // Referencia a transacciones
            $table->string('transaction_group_id')->nullable(); // Grupo de transacciones relacionadas
            $table->foreignId('main_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            
            // Metadatos adicionales
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['agreement_id']);
            $table->index(['service_request_id', 'service_offer_id']);
            $table->index(['payer_user_id']);
            $table->index(['payee_user_id']);
            $table->index(['status']);
            $table->index(['stripe_payment_intent_id']);
            $table->index(['stripe_session_id']);
            $table->index(['transaction_group_id']);
            $table->index(['main_transaction_id']);
            $table->index(['paid_at']);
            $table->index(['refunded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};