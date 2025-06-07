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

            // Vinculado a la oferta; NOT NULL y UNIQUE → 1 chat por oferta
            $table->foreignId('service_offer_id')
                  ->constrained('service_offers')
                  ->cascadeOnDelete();
            $table->unique('service_offer_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
