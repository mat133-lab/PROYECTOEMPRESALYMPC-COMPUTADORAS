<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_chat_id')->unique();
            $table->string('telegram_user_id')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('state')->nullable();
            $table->string('categoria')->nullable();
            $table->string('cita_fecha')->nullable();
            $table->string('contacto_tipo')->nullable();
            $table->text('payload')->nullable();
            $table->timestamp('ultima_actividad_en')->nullable();
            $table->timestamps();

            $table->index('id_usuario');
            $table->index('telegram_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_sessions');
    }
};
