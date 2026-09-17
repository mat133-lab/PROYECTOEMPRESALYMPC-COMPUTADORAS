<?php

namespace App\Http\Controllers;

use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramBotService $bot): JsonResponse
    {
        $secret = config('services.telegram.webhook_secret');
        abort_unless(is_string($secret) && $secret !== '' && hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token')), 403);
        $update = $request->validate(['update_id' => ['required', 'integer'], 'message' => ['nullable', 'array'], 'callback_query' => ['nullable', 'array']]);
        try { DB::table('telegram_webhook_updates')->insert(['update_id' => $update['update_id']]); }
        catch (\Illuminate\Database\QueryException) { return response()->json(['ok' => true]); }
        // aqui estamos haciendo que si el update es un callback_query, se maneje con handleCallback, y si no, se maneje con handle. 
        // Esto permite que el bot responda a ambos tipos de actualizaciones de Telegram.
        if (isset($update['callback_query'])) { $bot->handleCallback($update['callback_query']); }
        else { $bot->handle($update['message'] ?? []); }
        return response()->json(['ok' => true]);
    }
}
