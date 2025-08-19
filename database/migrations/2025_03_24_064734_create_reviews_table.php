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
        Schema::create('reviews', function (Blueprint $table) {
            // --- Campos Originales ---
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // Usuario que deja la reseña
            $table->foreignId('reviewed_id')->constrained('users')->onDelete('cascade'); // Usuario que recibe la reseña
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade'); // Servicio asociado
            $table->integer('rating')->default(5); // Puntuación de 1 a 5
            $table->text('comment')->nullable(); // Comentario opcional
            
            // --- Nuevos Campos Integrados ---
            $table->json('aspects')->nullable(); // Aspectos evaluados (ej: ['Calidad', 'Comunicación'])
            $table->json('aspects_ratings')->nullable(); // Ratings por aspecto (ej: {'Calidad': 5, 'Comunicación': 4})
            $table->boolean('would_recommend')->default(true); // ¿Recomendaría al usuario?
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved'); // Estado de la reseña
            $table->text('admin_notes')->nullable(); // Notas internas de moderación
            $table->timestamp('moderated_at')->nullable(); // Fecha de moderación
            $table->foreignId('moderated_by')->nullable()->constrained('users'); // Quién moderó
            $table->boolean('is_featured')->default(false); // Para destacar reseñas en la web
            $table->integer('helpful_votes')->default(0); // Contador de votos "útil"
            
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