<?php

namespace App\Http\Controllers;

use App\Models\TecnicoCategoria;
use Illuminate\Http\Request;

class TecnicoCategoriaController extends Controller
{
    public function viewIndex()
    {
        return view('technical.categories');
    }
    
    /**
     * Listar categorías activas (puedes agregar filtro para inactivos).
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto solo activos

        $query = TecnicoCategoria::query();

        if ($estado === '1') {
            $query->where('tecnico_categoria_estado', 1);
        } elseif ($estado === '0') {
            $query->where('tecnico_categoria_estado', 0);
        } // Si no filtras (por ejemplo 'all'), muestra todo

        $categorias = $query->orderBy('tecnico_categoria_id', 'desc')->get();

        return response()->json($categorias);
    }

    /**
     * Guardar nueva categoría.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tecnico_categoria_nombre' => 'required|string|max:255|unique:tecnico_categoria,tecnico_categoria_nombre',
        ]);

        $categoria = TecnicoCategoria::create([
            'tecnico_categoria_nombre' => $validated['tecnico_categoria_nombre'],
            'tecnico_categoria_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Categoría creada', 'categoria' => $categoria]);
    }

    /**
     * Mostrar categoría por id.
     */
    public function show($id)
    {
        $categoria = TecnicoCategoria::findOrFail($id);
        return response()->json($categoria);
    }

    /**
     * Actualizar categoría.
     */
    public function update(Request $request, $id)
    {
        $categoria = TecnicoCategoria::findOrFail($id);

        $validated = $request->validate([
            'tecnico_categoria_nombre' => 'required|string|max:255|unique:tecnico_categoria,tecnico_categoria_nombre,' . $categoria->tecnico_categoria_id . ',tecnico_categoria_id',
        ]);

        $categoria->update([
            'tecnico_categoria_nombre' => $validated['tecnico_categoria_nombre'],
        ]);

        return response()->json(['message' => 'Categoría actualizada', 'categoria' => $categoria]);
    }

    /**
     * Soft delete lógico (cambiar estado).
     */
    public function destroy($id)
    {
        $categoria = TecnicoCategoria::findOrFail($id);

        // Toggle estado (activo/inactivo)
        $categoria->tecnico_categoria_estado = $categoria->tecnico_categoria_estado ? 0 : 1;
        $categoria->save();

        $mensaje = $categoria->tecnico_categoria_estado ? 'Categoría activada' : 'Categoría desactivada';

        return response()->json(['message' => $mensaje, 'categoria' => $categoria]);
    }
}
