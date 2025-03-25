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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que crea el ticket
            $table->enum('category', ['account', 'payment', 'technical', 'other']); // Categoría del problema
            $table->text('message'); // Descripción del problema
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open'); // Estado del ticket
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
