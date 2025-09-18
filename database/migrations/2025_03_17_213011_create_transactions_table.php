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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Campos para agrupación y jerarquía de transacciones
            $table->uuid('transaction_group_id')->nullable();
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            
            // Usuario asociado a la transacción
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Relación polimórfica
            $table->string('related_model_type')->nullable();
            $table->unsignedBigInteger('related_model_id')->nullable();
            
            // Tipo de transacción expandido
            $table->enum('type', [
                'payment',           // Pago de cliente a helper
                'refund',            // Reembolso de helper a cliente
                'commission',        // Comisión de plataforma
                'withdrawal',        // Retiro de helper
                'deposit',           // Depósito a helper
                'bonus',             // Bonificación
                'fee',               // Tarifa adicional
                'penalty',           // Penalización
                'chargeback',        // Contracargo
                'adjustment',        // Ajuste manual
                'escrow_hold',       // Retención en escrow
                'escrow_release'     // Liberación de escrow
            ]);
            
            // Estado expandido
            $table->enum('status', [
                'pending',              // Pendiente de procesamiento
                'processing',           // En procesamiento
                'completed',            // Completada exitosamente
                'failed',               // Falló el procesamiento
                'cancelled',            // Cancelada
                'disputed',             // En disputa
                'refunded',             // Reembolsada
                'partially_refunded',   // Parcialmente reembolsada
                'held',                 // Retenida (escrow)
                'released'              // Liberada de retención
            ])->default('pending');
            
            // Campos financieros
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('fee_amount', 10, 2)->default(0); // Comisiones aplicadas
            $table->decimal('net_amount', 15, 2)->nullable(); // Monto neto después de comisiones
            $table->decimal('exchange_rate', 10, 6)->nullable(); // Para conversiones de moneda
            $table->decimal('original_amount', 15, 2)->nullable(); // Monto original antes de conversión
            $table->string('original_currency', 3)->nullable(); // Moneda original
            
            // Campos de método de pago y proveedor
            $table->string('payment_method')->nullable(); // stripe, paypal, bank_transfer
            $table->string('payment_provider_id')->nullable(); // ID del proveedor de pago
            $table->json('payment_provider_data')->nullable(); // Metadatos del proveedor
            
            // Descripción y referencia
            $table->string('description')->nullable();
            $table->string('reference')->nullable()->unique(); // Referencia única de transacción
            
            // Campos de liquidación y reconciliación
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('settlement_date')->nullable(); // Fecha de liquidación
            $table->enum('reconciliation_status', ['pending', 'reconciled', 'disputed'])->default('pending');
            
            // Campos de riesgo y compliance
            $table->integer('risk_score')->nullable(); // Puntuación de riesgo
            $table->enum('compliance_status', ['approved', 'under_review', 'rejected'])->default('approved');
            
            // Metadatos adicionales
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['transaction_group_id']);
            $table->index(['parent_transaction_id']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
            $table->index(['related_model_type', 'related_model_id']);
            $table->index(['payment_method']);
            $table->index(['payment_provider_id']);
            $table->index(['reconciliation_status']);
            $table->index(['compliance_status']);
            $table->index(['settlement_date']);
            $table->index(['type', 'status']);
            $table->index(['created_at', 'type']);
            $table->index('processed_at');
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};