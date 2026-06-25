<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversacion extends Model
{
    protected $table = 'chat_conversaciones';

    protected $primaryKey = 'id_conversacion';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    public function mensajes()
    {
        return $this->hasMany(ChatMensaje::class, 'id_conversacion', 'id_conversacion');
    }
}
