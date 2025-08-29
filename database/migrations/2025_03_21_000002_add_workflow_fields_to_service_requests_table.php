<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('started_at');
            $table->timestamp('client_confirmed_at')->nullable()->after('submitted_at');
            $table->timestamp('completed_at')->nullable()->after('client_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
            if (Schema::hasColumn('service_requests', 'client_confirmed_at')) {
                $table->dropColumn('client_confirmed_at');
            }
            if (Schema::hasColumn('service_requests', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};