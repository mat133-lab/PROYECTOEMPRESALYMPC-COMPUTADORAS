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
        if (! Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->increments('id_usuario');
                $table->string('usuario', 200);
                $table->string('correo', 255)->unique();
                $table->string('cedula', 20)->nullable();
                $table->string('archivo_ruc')->nullable();
                $table->string('archivo_cedula')->nullable();
                $table->string('contraseña', 150);
                $table->string('rol', 100);
                $table->string('reset_token')->nullable();
                $table->dateTime('reset_expiration')->nullable();
                $table->boolean('email_verified')->default(false);
                $table->string('email_verification_token')->nullable();
                $table->dateTime('email_verification_expires')->nullable();
                $table->string('foto_perfil')->nullable();
            });
        }

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('usuarios');
    }
};
