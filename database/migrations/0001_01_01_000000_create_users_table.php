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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('preferred_language', 2)->default('en')->nullable();
            $table->timestamp('email_verified_at')->nullable();  // Fecha de verificación del correo
            $table->string('password');
            $table->boolean('is_active')->default(true);  // Estado del usuario
            $table->boolean('accepted_terms')->default(false);  // Aceptó términos y condiciones
            $table->string('profile_photo_url')->nullable();  // URL de la foto de perfil
            $table->string('profile_video_url')->nullable();  // URL del video del perfil

            // Nuevos campos para biografía y documentos
            $table->text('biography')->nullable();
            $table->json('verification_documents')->nullable(); // Almacena las URL de documentos verificables
            $table->timestamp('documents_verified_at')->nullable();
            $table->string('verification_status')->default('pending'); // pending, verified, rejected
            $table->text('verification_notes')->nullable(); // Notas del administrador sobre la verificación

            // phone
            $table->string('phone')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('two_factor_authentication_code')->nullable();
            $table->timestamp('two_factor_authentication_code_sent_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_logout_at')->nullable();
            $table->timestamp('last_password_reset_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('last_ip_address_change_at')->nullable();
            $table->timestamp('last_device_id_change_at')->nullable();
            $table->timestamp('last_browser_id_change_at')->nullable();
            $table->timestamp('last_two_factor_authentication_code_change_at')->nullable();

            // Información de ubicación
            $table->string('address')->nullable();
            $table->string('zip_code')->nullable();  // Código postal
            $table->float('latitude', 10)->nullable();
            $table->float('longitude', 10)->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
