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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->constrained('users')->onDelete('cascade'); // Cliente que paga
            $table->foreignId('payee_id')->constrained('users')->onDelete('cascade'); // Helper que recibe el pago
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade'); // Servicio asociado
            $table->decimal('amount', 10, 2);
            $table->decimal('system_fee', 10, 2)->default(0.00); // Comisión del sistema
            $table->decimal('final_amount', 10, 2); // Monto recibido por el helper después de la comisión
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
           
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->string('transaction_id')->nullable(); // ID de la pasarela de pago
            $table->timestamps();
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
