<?php

namespace App\Mail;

use App\Models\Contacto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TelegramAdvisorNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Contacto $contacto) {}

    public function build(): self
    {
        return $this
            ->replyTo((string) $this->contacto->correo, (string) $this->contacto->nombre)
            ->subject('Nueva solicitud de Telegram #' . $this->contacto->id_mensaje)
            ->text('emails.telegram-advisor-notification', ['contacto' => $this->contacto]);
    }
}
