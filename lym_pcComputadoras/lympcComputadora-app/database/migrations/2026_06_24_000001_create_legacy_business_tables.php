<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureUsuariosColumns();

        if (! Schema::hasTable('soporte')) {
            Schema::create('soporte', function (Blueprint $table) {
                $table->increments('id_soporte');
                $table->string('Nombre');
                $table->string('Correo');
                $table->string('cedula', 20)->nullable();
                $table->string('archivo_ruc')->nullable();
                $table->string('archivo_cedula')->nullable();
                $table->string('Compania');
                $table->string('Mensaje');
            });
        }

        if (! Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->increments('id_producto');
                $table->string('nombre');
                $table->string('serie', 100)->unique();
                $table->date('fecha');
                $table->integer('unidades');
                $table->decimal('precio', 10, 2);
                $table->string('imagen');
                $table->unsignedInteger('id_soporte')->nullable();
                $table->string('categoria');

                $table->foreign('id_soporte')
                    ->references('id_soporte')
                    ->on('soporte')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('citas')) {
            Schema::create('citas', function (Blueprint $table) {
                $table->increments('id_cita');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('nombre');
                $table->string('apellido');
                $table->string('correo');
                $table->string('cedula', 20)->nullable();
                $table->string('archivo_ruc')->nullable();
                $table->string('archivo_cedula')->nullable();
                $table->boolean('accion_sensible')->default(false);
                $table->date('fecha');
                $table->string('telefono', 25);
                $table->string('motivo', 500);

                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('contacto')) {
            Schema::create('contacto', function (Blueprint $table) {
                $table->increments('id_mensaje');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('nombre');
                $table->string('correo');
                $table->string('compania');
                $table->text('mensaje');
                $table->string('cedula', 20)->nullable();
                $table->string('archivo_ruc')->nullable();
                $table->string('archivo_cedula')->nullable();
                $table->timestamp('fecha')->useCurrent();

                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('pedidos')) {
            Schema::create('pedidos', function (Blueprint $table) {
                $table->increments('id_pedido');
                $table->unsignedInteger('id_usuario');
                $table->dateTime('fecha_pedido')->useCurrent();
                $table->decimal('total', 10, 2);

                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('detalles_pedido')) {
            Schema::create('detalles_pedido', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedInteger('id_pedido');
                $table->unsignedInteger('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 10, 2);

                $table->foreign('id_pedido')
                    ->references('id_pedido')
                    ->on('pedidos')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreign('id_producto')
                    ->references('id_producto')
                    ->on('productos')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('chat_conversaciones')) {
            Schema::create('chat_conversaciones', function (Blueprint $table) {
                $table->increments('id_conversacion');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('nombre_usuario', 200);
                $table->string('correo_usuario')->nullable();
                $table->string('estado', 40)->default('ia');
                $table->string('tema', 180)->nullable();
                $table->tinyInteger('calificacion')->nullable();
                $table->text('comentario_calificacion')->nullable();
                $table->dateTime('fecha_cierre')->nullable();
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('chat_mensajes')) {
            Schema::create('chat_mensajes', function (Blueprint $table) {
                $table->increments('id_mensaje');
                $table->unsignedInteger('id_conversacion');
                $table->string('remitente', 40);
                $table->string('nombre_remitente', 200);
                $table->text('mensaje');
                $table->timestamp('fecha_envio')->useCurrent();

                $table->index('id_conversacion');
            });
        }

        if (! Schema::hasTable('notificaciones')) {
            Schema::create('notificaciones', function (Blueprint $table) {
                $table->increments('id_notificacion');
                $table->unsignedInteger('id_remitente')->nullable();
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('titulo', 180);
                $table->text('mensaje');
                $table->string('correo_destino');
                $table->string('estado_correo', 30)->default('pendiente');
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('chat_mensajes');
        Schema::dropIfExists('chat_conversaciones');
        Schema::dropIfExists('detalles_pedido');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('contacto');
        Schema::dropIfExists('citas');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('soporte');
    }

    private function ensureUsuariosColumns(): void
    {
        if (! Schema::hasTable('usuarios')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'email_verified')) {
                $table->boolean('email_verified')->default(false);
            }

            if (! Schema::hasColumn('usuarios', 'email_verification_token')) {
                $table->string('email_verification_token')->nullable();
            }

            if (! Schema::hasColumn('usuarios', 'email_verification_expires')) {
                $table->dateTime('email_verification_expires')->nullable();
            }

            if (! Schema::hasColumn('usuarios', 'foto_perfil')) {
                $table->string('foto_perfil')->nullable();
            }
        });
    }
};
