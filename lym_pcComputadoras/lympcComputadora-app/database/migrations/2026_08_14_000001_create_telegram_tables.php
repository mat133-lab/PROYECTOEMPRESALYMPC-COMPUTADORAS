<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_usuario')->unique();
            $table->string('telegram_chat_id', 32)->unique();
            $table->string('telegram_user_id', 32)->unique();
            $table->string('username')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->timestamp('telefono_verificado_en')->nullable();
            $table->timestamp('ultima_actividad_en')->nullable();
            $table->timestamps();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });

        Schema::create('telegram_link_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_usuario')->index();
            $table->string('codigo_hash', 64)->unique();
            $table->timestamp('expira_en');
            $table->timestamp('usado_en')->nullable();
            $table->string('ip_solicitud', 45)->nullable();
            $table->timestamps();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });

        Schema::create('telegram_webhook_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('update_id')->unique();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_webhook_updates');
        Schema::dropIfExists('telegram_link_codes');
        Schema::dropIfExists('telegram_accounts');
    }
};
