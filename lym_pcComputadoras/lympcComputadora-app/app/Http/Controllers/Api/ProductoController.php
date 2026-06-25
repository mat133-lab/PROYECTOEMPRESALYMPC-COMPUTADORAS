<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Producto::query();

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->input('categoria'));
        }

        if ($request->filled('buscar')) {
            $search = $request->input('buscar');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('serie', 'like', "%{$search}%");
            });
        }

        $productos = $query->paginate(15);

        return ProductoResource::collection($productos)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'serie' => ['nullable', 'string', 'max:255'],
            'fecha' => ['nullable', 'date'],
            'unidades' => ['required', 'integer', 'min:0'],
            'precio' => ['required', 'numeric', 'min:0'],
            'categoria' => ['required', 'string', 'max:100'],
            'imagen' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = 'img_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public', $filename);
            $validated['imagen'] = $filename;
        }

        $producto = Producto::create($validated);

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'data' => new ProductoResource($producto),
        ], 201);
    }
}