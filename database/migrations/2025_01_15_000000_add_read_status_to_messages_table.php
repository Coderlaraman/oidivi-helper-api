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
        Schema::table('messages', function (Blueprint $table) {
            // Agregar campo para indicar cuándo el mensaje fue leído por el destinatario
            $table->timestamp('read_at')->nullable()->after('seen_at');
            
            // Agregar índice para optimizar consultas de mensajes leídos
            $table->index(['sender_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id', 'read_at']);
            $table->dropColumn('read_at');
        });
    }
};