<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramAccount extends Model
{
    protected $fillable = ['id_usuario', 'telegram_chat_id', 'telegram_user_id', 'username', 'telefono', 'telefono_verificado_en', 'ultima_actividad_en'];

    protected function casts(): array
    {
        return ['telefono_verificado_en' => 'datetime', 'ultima_actividad_en' => 'datetime'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
