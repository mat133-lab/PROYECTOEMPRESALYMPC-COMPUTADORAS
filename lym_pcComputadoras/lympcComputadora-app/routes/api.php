<?php

use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/productos', [ProductoController::class, 'store']);
Route::post('/telegram/webhook', TelegramWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('telegram.webhook');
