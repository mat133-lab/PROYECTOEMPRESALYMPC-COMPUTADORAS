<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramSession extends Model
{
    protected $table = 'telegram_sessions';

    protected $fillable = [
        'telegram_chat_id',
        'telegram_user_id',
        'id_usuario',
        'state',
        'categoria',
        'cita_fecha',
        'contacto_tipo',
        'payload',
        'ultima_actividad_en',
    ];

    protected $casts = [
        'ultima_actividad_en' => 'datetime',
        'payload' => 'array',
    ];
}
