<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMensaje extends Model
{
    protected $table = 'chat_mensajes';

    protected $primaryKey = 'id_mensaje';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha_envio' => 'datetime',
        ];
    }

    public function conversacion()
    {
        return $this->belongsTo(ChatConversacion::class, 'id_conversacion', 'id_conversacion');
    }
}
