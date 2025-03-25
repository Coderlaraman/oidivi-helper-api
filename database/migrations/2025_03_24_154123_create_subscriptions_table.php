<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario suscriptor
            $table->string('plan_name'); // Nombre del plan (ej. "Básico", "Premium")
            $table->enum('status', ['active', 'canceled', 'expired'])->default('active'); // Estado de la suscripción
            $table->decimal('price', 10, 2); // Precio del plan
            $table->timestamp('start_date')->nullable(); // Fecha de inicio
            $table->timestamp('end_date')->nullable();   // Fecha de finalización
            $table->json('details')->nullable(); // Información adicional, como características del plan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
