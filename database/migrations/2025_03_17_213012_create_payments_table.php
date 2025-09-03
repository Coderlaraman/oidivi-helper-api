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
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('payer_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payee_user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', [
                'pending',
                'processing', 
                'completed',
                'failed',
                'canceled',
                'refunded'
            ])->default('pending');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->json('stripe_metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['contract_id']);
            $table->index(['service_request_id', 'service_offer_id']);
            $table->index(['payer_user_id']);
            $table->index(['payee_user_id']);
            $table->index(['status']);
            $table->index(['stripe_payment_intent_id']);
            $table->index(['stripe_session_id']);
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