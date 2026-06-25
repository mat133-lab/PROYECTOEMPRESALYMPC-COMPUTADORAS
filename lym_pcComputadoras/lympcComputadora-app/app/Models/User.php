<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'usuario',
        'correo',
        'cedula',
        'archivo_ruc',
        'archivo_cedula',
        'contraseña',
        'rol',
        'reset_token',
        'reset_expiration',
        'email_verified',
        'email_verification_token',
        'email_verification_expires',
        'foto_perfil',
    ];

    protected $hidden = [
        'contraseña',
        'reset_token',
        'email_verification_token',
    ];

    protected function casts(): array
    {
        return [
            'reset_expiration' => 'datetime',
            'email_verified' => 'boolean',
            'email_verification_expires' => 'datetime',
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'id_usuario';
    }

    public function getAuthPassword(): string
    {
        return (string) $this->getAttribute('contraseña');
    }

    public function getEmailForPasswordReset(): string
    {
        return (string) $this->correo;
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_usuario', 'id_usuario');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_usuario', 'id_usuario');
    }
}
