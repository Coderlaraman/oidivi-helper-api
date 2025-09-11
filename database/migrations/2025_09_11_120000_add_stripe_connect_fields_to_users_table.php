<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable()->unique()->after('longitude');
            }
            if (!Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->unique()->after('stripe_account_id');
            }
            if (!Schema::hasColumn('users', 'stripe_charges_enabled')) {
                $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_customer_id');
            }
            if (!Schema::hasColumn('users', 'stripe_payouts_enabled')) {
                $table->boolean('stripe_payouts_enabled')->default(false)->after('stripe_charges_enabled');
            }
            if (!Schema::hasColumn('users', 'stripe_onboarded_at')) {
                $table->timestamp('stripe_onboarded_at')->nullable()->after('stripe_payouts_enabled');
            }
            if (!Schema::hasColumn('users', 'stripe_requirements')) {
                $table->json('stripe_requirements')->nullable()->after('stripe_onboarded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'stripe_requirements')) {
                $table->dropColumn('stripe_requirements');
            }
            if (Schema::hasColumn('users', 'stripe_onboarded_at')) {
                $table->dropColumn('stripe_onboarded_at');
            }
            if (Schema::hasColumn('users', 'stripe_payouts_enabled')) {
                $table->dropColumn('stripe_payouts_enabled');
            }
            if (Schema::hasColumn('users', 'stripe_charges_enabled')) {
                $table->dropColumn('stripe_charges_enabled');
            }
            if (Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->dropColumn('stripe_customer_id');
            }
            if (Schema::hasColumn('users', 'stripe_account_id')) {
                $table->dropColumn('stripe_account_id');
            }
        });
    }
};