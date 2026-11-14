<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('paypal_pedidos')) {
            Schema::create('paypal_pedidos', function (Blueprint $table) {
                $table->string('paypal_order_id', 64)->primary();
                $table->unsignedInteger('id_pedido')->unique();
                $table->unsignedInteger('id_usuario');
                $table->decimal('total', 10, 2);
                $table->timestamp('fecha_registro')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('paypal_pedidos');
    }
};
