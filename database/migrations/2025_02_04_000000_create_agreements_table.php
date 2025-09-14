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
        Schema::create('agreements', function (Blueprint $table) {
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
                'expired',
                'completed'
            ])->default('draft');
            $table->json('terms')->nullable(); // Términos del contrato en formato JSON
            $table->timestamp('sent_at')->nullable(); // Cuando se envió al provider
            $table->timestamp('responded_at')->nullable(); // Cuando el provider respondió
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración del contrato
            $table->timestamp('completed_at')->nullable(); // Cuando se completó el contrato
            $table->text('rejection_reason')->nullable(); // Razón del rechazo si aplica
            $table->text('cancellation_reason')->nullable(); // Razón de cancelación si aplica
            
            // Campos de revisión
            $table->unsignedInteger('version')->default(1); // Versión del contrato
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete(); // Usuario que editó
            $table->timestamp('edited_at')->nullable(); // Cuando se editó
            $table->timestamp('re_sent_at')->nullable(); // Cuando se reenvió
            $table->string('revision_note', 500)->nullable(); // Nota de revisión
            
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['service_request_id', 'service_offer_id']);
            $table->index(['client_id']);
            $table->index(['provider_id']);
            $table->index(['status']);
            $table->index(['expires_at']);
            $table->index(['version']);
            
            // Constraint único: solo un acuerdo por oferta
            $table->unique('service_offer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};