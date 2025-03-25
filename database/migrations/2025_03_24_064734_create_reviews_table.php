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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // Usuario que deja la reseña
            $table->foreignId('reviewed_id')->constrained('users')->onDelete('cascade'); // Usuario que recibe la reseña
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade'); // Servicio asociado
            $table->integer('rating')->default(5); // Puntuación de 1 a 5
            $table->text('comment')->nullable(); // Comentario opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
