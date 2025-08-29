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
        Schema::table('service_requests', function (Blueprint $table) {
            // Helper asignado al request tras el pago
            $table->foreignId('assigned_helper_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            // Fecha/hora de inicio del trabajo una vez pagado
            $table->timestamp('started_at')
                ->nullable()
                ->after('status');

            // Nota: otros campos (submitted_at, client_confirmed_at) se añadirán en fases posteriores
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'assigned_helper_id')) {
                $table->dropConstrainedForeignId('assigned_helper_id');
            }
            if (Schema::hasColumn('service_requests', 'started_at')) {
                $table->dropColumn('started_at');
            }
        });
    }
};