<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramActionRequest extends Model
{
    protected $fillable = ['id_usuario', 'telegram_chat_id', 'accion', 'datos', 'resumen', 'confirmacion_hash', 'estado', 'expira_en', 'confirmada_en', 'error'];
    protected function casts(): array { return ['datos' => 'array', 'expira_en' => 'datetime', 'confirmada_en' => 'datetime']; }
}
