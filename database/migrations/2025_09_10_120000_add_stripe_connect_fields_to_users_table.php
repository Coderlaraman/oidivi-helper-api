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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable()->after('remember_token');
            $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_account_id');
            $table->boolean('stripe_payouts_enabled')->default(false)->after('stripe_charges_enabled');
            $table->json('stripe_account_status')->nullable()->after('stripe_payouts_enabled');

            $table->index('stripe_account_id');
            $table->index(['stripe_charges_enabled', 'stripe_payouts_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['stripe_account_id']);
            $table->dropIndex(['stripe_charges_enabled', 'stripe_payouts_enabled']);

            $table->dropColumn([
                'stripe_account_id',
                'stripe_charges_enabled',
                'stripe_payouts_enabled',
                'stripe_account_status',
            ]);
        });
    }
};