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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('type')->default('text'); // text, image, file, system
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->json('metadata')->nullable(); // Para datos adicionales como reacciones, etc.
            $table->timestamp('seen_at')->nullable(); // Reemplaza el booleano seen por un timestamp
            $table->timestamps();
            $table->softDeletes(); // Para permitir borrar mensajes sin perderlos
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
