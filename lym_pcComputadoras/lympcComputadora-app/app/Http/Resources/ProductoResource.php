<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id_producto,
            'nombre' => $this->nombre,
            'serie' => $this->serie,
            'fecha' => $this->fecha?->format('Y-m-d'),
            'unidades' => $this->unidades,
            'precio' => (float) $this->precio,
            'imagen' => $this->imagen,
            'categoria' => $this->categoria,
        ];
    }
}