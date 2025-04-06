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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one')->constrained('users')->onDelete('cascade'); // Usuario 1
            $table->foreignId('user_two')->constrained('users')->onDelete('cascade'); // Usuario 2
            $table->foreignId('service_request_id')->nullable()->constrained()->onDelete('set null'); // Solicitud de servicio asociada
            $table->boolean('is_group')->default(false); // Indica si es un chat grupal
            $table->string('name')->nullable(); // Nombre del chat (para grupos)
            $table->text('description')->nullable(); // Descripción del chat (para grupos)
            $table->timestamp('last_message_at')->nullable(); // Fecha del último mensaje
            $table->timestamps();
            $table->softDeletes(); // Permite borrado suave
        });

        // Tabla para participantes en chats grupales
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_admin')->default(false); // Indica si es administrador del grupo
            $table->timestamp('last_read_at')->nullable(); // Última vez que leyó mensajes
            $table->timestamps();

            // Un usuario solo puede estar una vez en un chat
            $table->unique(['chat_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chats');
    }
};
