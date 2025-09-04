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
        Schema::table('service_offers', function (Blueprint $table) {
            // Modificar la columna enum para incluir 'in_review'
            $table->enum('status', ['pending', 'accepted', 'rejected', 'in_review'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_offers', function (Blueprint $table) {
            // Revertir la columna enum a los valores originales
            $table->enum('status', ['pending', 'accepted', 'rejected'])
                  ->default('pending')
                  ->change();
        });
    }
};
