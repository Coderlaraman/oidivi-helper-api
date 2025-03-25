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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Cliente (quien publicó la solicitud)
            $table->foreignId('assigned_user_id')->constrained('users')->onDelete('cascade'); // Helper (quien ejecuta el servicio)
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'disputed'])->default('active');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
