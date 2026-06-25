<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soporte extends Model
{
    protected $table = 'soporte';

    protected $primaryKey = 'id_soporte';

    public $timestamps = false;

    protected $guarded = [];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_soporte', 'id_soporte');
    }
}
