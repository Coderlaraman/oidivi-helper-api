<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Relación con chats (1 chat → N mensajes)
            $table->foreignId('chat_id')
                  ->constrained('chats')
                  ->cascadeOnDelete();

            // Quién envía (usuario autenticado)
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Texto libre (nullable si es solo multimedia)
            $table->text('message')->nullable();

            // Tipo de mensaje para distinguir texto/images/videos/archivos
            $table->enum('type', ['text', 'image', 'video', 'file', 'system'])
                  ->default('text');

            // Si hay archivo adjunto, aquí se guarda la URL (S3, disco, etc.)
            $table->string('media_url')->nullable();

            // MIME o categoría del archivo adjunto (p.ej. 'image/jpeg', 'video/mp4', 'application/pdf')
            $table->string('media_type')->nullable();

            // Nombre original del archivo (opcional, pero recomendado)
            $table->string('media_name')->nullable();

            // JSON para datos adicionales: dimensiones, duración, miniatura, reacciones, etc.
            $table->json('metadata')->nullable();

            // Timestamp de “vistos”
            $table->timestamp('seen_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
