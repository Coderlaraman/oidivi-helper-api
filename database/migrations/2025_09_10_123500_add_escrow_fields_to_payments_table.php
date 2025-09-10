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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'stripe_transfer_id')) {
                $table->string('stripe_transfer_id')->nullable()->after('stripe_session_id');
                $table->index('stripe_transfer_id');
            }
            if (!Schema::hasColumn('payments', 'platform_fee_percent')) {
                $table->unsignedInteger('platform_fee_percent')->nullable()->after('stripe_transfer_id');
            }
            if (!Schema::hasColumn('payments', 'platform_fee_amount')) {
                $table->decimal('platform_fee_amount', 10, 2)->nullable()->after('platform_fee_percent');
            }
            if (!Schema::hasColumn('payments', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('paid_at');
                $table->index('released_at');
            }
        });

        // Agregar nuevos estados 'held' y 'released' al ENUM de status (solo en MySQL)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `payments` MODIFY COLUMN `status` 
                ENUM('pending','processing','completed','failed','canceled','refunded','held','released') 
                NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir enum para remover 'held' y 'released' (solo en MySQL)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `payments` MODIFY COLUMN `status` 
                ENUM('pending','processing','completed','failed','canceled','refunded') 
                NOT NULL DEFAULT 'pending'");
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'released_at')) {
                $table->dropIndex(['released_at']);
                $table->dropColumn('released_at');
            }
            if (Schema::hasColumn('payments', 'platform_fee_amount')) {
                $table->dropColumn('platform_fee_amount');
            }
            if (Schema::hasColumn('payments', 'platform_fee_percent')) {
                $table->dropColumn('platform_fee_percent');
            }
            if (Schema::hasColumn('payments', 'stripe_transfer_id')) {
                $table->dropIndex(['stripe_transfer_id']);
                $table->dropColumn('stripe_transfer_id');
            }
        });
    }
};