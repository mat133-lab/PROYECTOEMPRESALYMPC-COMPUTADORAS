<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_action_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_usuario')->index();
            $table->string('telegram_chat_id', 32);
            $table->string('accion', 80);
            $table->json('datos');
            $table->string('resumen', 1000);
            $table->string('confirmacion_hash', 64)->unique();
            $table->string('estado', 24)->default('pendiente')->index();
            $table->timestamp('expira_en');
            $table->timestamp('confirmada_en')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->cascadeOnDelete();
        });

        Schema::create('telegram_action_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_usuario')->nullable()->index();
            $table->string('telegram_chat_id', 32)->nullable();
            $table->string('accion', 80);
            $table->string('estado', 30);
            $table->json('detalle')->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_action_audits');
        Schema::dropIfExists('telegram_action_requests');
    }
};
