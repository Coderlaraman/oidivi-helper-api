<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('cascade');
            
            // Campos del método de pago
            $table->string('type'); // card, bank_transfer, paypal, etc.
            $table->string('provider'); // stripe, paypal, etc.
            $table->string('token')->nullable(); // Token o ID del método de pago en el proveedor
            $table->json('billing_details')->nullable();
            $table->boolean('is_default')->default(false);
            
            // Campos del log de pago
            $table->string('status')->default('pending'); // pending, completed, failed, refunded, etc.
            $table->string('event')->nullable(); // created, confirmed, refunded, etc.
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('details')->nullable(); // Detalles adicionales del pago
            
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['user_id', 'is_default']);
            $table->index(['transaction_id']);
            $table->index(['status']);
            $table->index(['provider']);
            $table->index(['type']);
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