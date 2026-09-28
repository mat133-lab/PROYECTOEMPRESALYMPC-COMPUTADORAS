<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TelegramActionExecutedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $title, public readonly string $description) {}

    public function build(): self
    {
        return $this->subject($this->title)->text('emails.telegram-action-text', ['description' => $this->description]);
    }
}
