<?php

use App\Http\Controllers\LegacyController;
use App\Http\Controllers\LegacyFileController;
use App\Http\Controllers\TelegramLinkController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/php/vistabeta.php');
Route::redirect('/login', '/php/login.php');
Route::redirect('/dashboard', '/php/dashboard.php');
Route::redirect('/admin', '/php/login_admin.php');

Route::get('/telegram', [TelegramLinkController::class, 'show'])->name('telegram.link');
Route::post('/telegram/codigo-vinculacion', [TelegramLinkController::class, 'createCode'])
    ->middleware('throttle:4,1')->name('telegram.link.code');
Route::post('/telegram/desvincular', [TelegramLinkController::class, 'unlink'])
    ->middleware('throttle:4,1')->name('telegram.unlink');

Route::any('/php/{script}', LegacyController::class)
    ->where('script', '[^/]+\.php')
    ->name('legacy.php');

Route::get('/uploads/{path}', [LegacyFileController::class, 'uploads'])
    ->where('path', '.*')
    ->name('legacy.uploads');

Route::get('/docs/{path}', [LegacyFileController::class, 'docs'])
    ->where('path', '.*')
    ->name('legacy.docs');

Route::get('/assets/{path}', [LegacyFileController::class, 'assets'])
    ->where('path', '.*')
    ->name('legacy.assets');
