<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PollTelegramUpdates extends Command
{
    protected $signature = 'telegram:poll {--daemon : Mantiene el proceso ejecutándose} {--timeout=25 : Espera larga de Telegram, entre 1 y 50 segundos}';
    protected $description = 'Recibe actualizaciones del bot por long polling; útil en desarrollo sin URL pública.';

    public function handle(TelegramBotService $bot): int
    {
        $token = config('services.telegram.bot_token');
        if (! $token) { $this->error('Falta TELEGRAM_BOT_TOKEN en .env.'); return self::FAILURE; }
        $timeout = min(50, max(1, (int) $this->option('timeout')));
        $offset = (int) (DB::table('telegram_webhook_updates')->max('update_id') ?? 0) + 1;
        $this->info('Polling activo. Deténlo con Ctrl+C. No funciona si el bot tiene un webhook activo.');

        do {
            try {
                $response = Http::timeout($timeout + 10)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset' => $offset, 'timeout' => $timeout, 'allowed_updates' => json_encode(['message', 'callback_query']),
                ])->throw()->json();
                foreach ($response['result'] ?? [] as $update) {
                    $updateId = (int) ($update['update_id'] ?? 0); if (! $updateId) continue;
                    try { DB::table('telegram_webhook_updates')->insert(['update_id' => $updateId]); }
                    catch (\Illuminate\Database\QueryException) { $offset = $updateId + 1; continue; }

                    if (isset($update['callback_query'])) {
                        $bot->handleCallback($update['callback_query']);
                    } elseif (isset($update['message'])) {
                        $bot->handle($update['message']);
                    }
                    $offset = $updateId + 1;
                }
            } catch (\Throwable $e) {
                report($e); $this->warn('No se pudo consultar Telegram; reintentando en 5 segundos.'); sleep(5);
            }
        } while ($this->option('daemon'));

        return self::SUCCESS;
    }
}
