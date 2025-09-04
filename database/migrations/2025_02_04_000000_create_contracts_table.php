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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', [
                'draft',
                'sent', 
                'accepted',
                'rejected',
                'cancelled',
                'expired'
            ])->default('draft');
            $table->json('terms')->nullable(); // Términos del contrato en formato JSON
            $table->timestamp('sent_at')->nullable(); // Cuando se envió al provider
            $table->timestamp('responded_at')->nullable(); // Cuando el provider respondió
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración del contrato
            $table->text('rejection_reason')->nullable(); // Razón del rechazo si aplica
            $table->text('cancellation_reason')->nullable(); // Razón de cancelación si aplica
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['service_request_id', 'service_offer_id']);
            $table->index(['client_id']);
            $table->index(['provider_id']);
            $table->index(['status']);
            $table->index(['expires_at']);
            
            // Constraint único: solo un contrato por oferta
            $table->unique('service_offer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};