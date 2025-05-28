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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('service_offer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable(); // Para chats grupales
            $table->string('type')->default('direct'); // direct, group
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Tabla pivot para participantes del chat
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_admin')->default(false); // Para chats grupales
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            
            // Índice único para evitar duplicados
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
