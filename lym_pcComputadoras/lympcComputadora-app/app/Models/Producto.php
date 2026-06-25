<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'precio' => 'decimal:2',
            'unidades' => 'integer',
        ];
    }

    public function soporte()
    {
        return $this->belongsTo(Soporte::class, 'id_soporte', 'id_soporte');
    }

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class, 'id_producto', 'id_producto');
    }
}
