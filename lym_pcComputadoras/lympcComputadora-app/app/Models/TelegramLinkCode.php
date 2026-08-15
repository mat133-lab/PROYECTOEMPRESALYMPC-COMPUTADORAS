<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramLinkCode extends Model
{
    protected $fillable = ['id_usuario', 'codigo_hash', 'expira_en', 'usado_en', 'ip_solicitud'];

    protected function casts(): array
    {
        return ['expira_en' => 'datetime', 'usado_en' => 'datetime'];
    }
}
