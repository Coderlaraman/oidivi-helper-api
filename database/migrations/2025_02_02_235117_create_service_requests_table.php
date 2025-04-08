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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('address');
            $table->string('zip_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('budget', 10, 2);
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->enum('status', ['published', 'in_progress', 'completed', 'canceled'])->default('published');
            $table->enum('payment_method', ['paypal', 'credit_card', 'bank_transfer'])->nullable();
            $table->enum('service_type', ['one_time', 'recurring'])->default('one_time');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium'); 
            $table->dateTime('due_date')->nullable(); 
            $table->json('metadata')->nullable(); 
            $table->softDeletes(); 
            $table->timestamps();

            // Índices compuestos para búsquedas eficientes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'visibility']);
            $table->index(['due_date', 'status']);
            $table->index(['latitude', 'longitude']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
