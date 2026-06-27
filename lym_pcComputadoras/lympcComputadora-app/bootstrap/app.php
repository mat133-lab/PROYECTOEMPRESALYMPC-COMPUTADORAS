<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: [
            'LEGACYSESSID',
            'PHPSESSID',
        ]);
        $middleware->validateCsrfTokens(except: [
            'php/api_horarios.php',
            'php/api_horarios_admin.php',
            'php/api_grafico_asistente.php',
            'php/chat_api.php',
            'php/cart_action.php',
            'php/eliminar_cita.php',
            'php/guardar_cita.php',
            'php/buscar_cita.php',
            'php/procesar_compra.php',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
