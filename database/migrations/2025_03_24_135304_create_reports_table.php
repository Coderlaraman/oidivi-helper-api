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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade'); // Quién reporta
            $table->foreignId('reported_user')->nullable()->constrained('users')->onDelete('cascade'); // Usuario reportado
            $table->foreignId('service_request_id')->nullable()->constrained()->onDelete('cascade'); // Servicio relacionado
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('cascade'); // Pago relacionado
            $table->enum('type', ['fraud', 'abuse', 'payment_issue', 'other']); // Tipo de reporte
            $table->text('description'); // Descripción del problema
            $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending'); // Estado del reporte
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
