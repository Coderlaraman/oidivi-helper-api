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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // Remitente
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('set null'); // Receptor (opcional para grupos)
            $table->text('message');
            $table->string('type')->default('text'); // Tipo de mensaje: text, image, video, audio, file, location
            $table->string('media_url')->nullable(); // URL del archivo multimedia
            $table->string('media_type')->nullable(); // Tipo de archivo multimedia
            $table->json('metadata')->nullable(); // Metadatos adicionales (coordenadas, duración, etc.)
            $table->boolean('seen')->default(false);
            $table->morphs('service_request'); // Relación polimórfica con solicitudes de servicio
            $table->timestamps();
            $table->softDeletes(); // Permite borrado suave

        // Índices para mejorar el rendimiento de las consultas
        $table->index(['chat_id', 'created_at']);
        $table->index(['sender_id', 'created_at']);
        $table->index(['receiver_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
