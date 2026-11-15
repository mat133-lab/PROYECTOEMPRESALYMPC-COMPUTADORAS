<?php

namespace App\Services;

use App\Mail\TelegramAdvisorNotificationMail;
use App\Models\Contacto;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TelegramAdvisorNotificationService
{
    public function send(Contacto $contacto): bool
    {
        $recipients = User::query()
            ->whereRaw('LOWER(rol) = ?', ['asistente'])
            ->whereNotNull('correo')
            ->pluck('correo')
            ->map(fn ($email): string => trim((string) $email))
            ->filter(fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();

        if ($recipients === []) {
            Log::warning('No hay cuentas de asistente con correo válido para avisar una solicitud de Telegram.', [
                'contact_id' => $contacto->id_mensaje,
            ]);
            return false;
        }

        try {
            $mail = new TelegramAdvisorNotificationMail($contacto);
            $pendingMail = Mail::to(array_shift($recipients));
            if ($recipients !== []) {
                $pendingMail->bcc($recipients);
            }
            $pendingMail->send($mail);

            return true;
        } catch (\Throwable $exception) {
            report($exception);
            Log::error('No se pudo enviar el aviso de una solicitud de Telegram a los asesores.', [
                'contact_id' => $contacto->id_mensaje,
                'recipient_count' => count($recipients) + 1,
                'exception' => $exception::class,
            ]);

            return false;
        }
    }
}
