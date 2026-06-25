<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'php/api_horarios.php',
        'php/api_horarios_admin.php',
        'php/api_grafico_asistente.php',
        'php/chat_api.php',
        'php/cart_action.php',
        'php/eliminar_cita.php',
        'php/guardar_cita.php',
        'php/buscar_cita.php',
        'php/procesar_compra.php',
    ];
}