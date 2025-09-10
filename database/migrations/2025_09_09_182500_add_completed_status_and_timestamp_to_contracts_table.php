<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Agregar columna completed_at si no existe
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'completed_at')) {
                // colocar después de expires_at para mantener orden lógico
                $table->timestamp('completed_at')->nullable()->after('expires_at');
            }
        });

        // 2) Agregar el estado 'completed' al enum de status (solo en MySQL)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `contracts` MODIFY COLUMN `status` 
                ENUM('draft','sent','accepted','rejected','cancelled','expired','completed') 
                NOT NULL DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir enum para remover 'completed' (solo en MySQL)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `contracts` MODIFY COLUMN `status` 
                ENUM('draft','sent','accepted','rejected','cancelled','expired') 
                NOT NULL DEFAULT 'draft'");
        }

        // Eliminar completed_at si existe
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};