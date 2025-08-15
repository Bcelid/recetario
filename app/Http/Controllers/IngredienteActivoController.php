<?php

namespace App\Http\Controllers;

use App\Models\IngredienteActivo;
use Illuminate\Http\Request;

class IngredienteActivoController extends Controller
{
    /**
     * Vista principal (opcional)
     */
    public function viewIndex()
    {
        return view('product.activeingredient'); // Cambia según la ruta de tu vista
    }

    /**
     * Listar ingredientes filtrando por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto activos

        $query = IngredienteActivo::query();

        if ($estado === '1') {
            $query->where('ingrediente_activo_estado', 1);
        } elseif ($estado === '0') {
            $query->where('ingrediente_activo_estado', 0);
        }

        $ingredientes = $query->orderBy('ingrediente_activo_id', 'desc')->get();

        return response()->json($ingredientes);
    }

    /**
     * Guardar un nuevo ingrediente activo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ingrediente_activo_nombre' => 'required|string|max:255|unique:ingredienteactivo,ingrediente_activo_nombre',
            'ingrediente_activo_detalle' => 'nullable|string',
        ]);

        $ingrediente = IngredienteActivo::create([
            'ingrediente_activo_nombre' => $validated['ingrediente_activo_nombre'],
            'ingrediente_activo_detalle' => $validated['ingrediente_activo_detalle'] ?? null,
            'ingrediente_activo_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Ingrediente activo creado', 'ingrediente' => $ingrediente]);
    }

    /**
     * Mostrar un ingrediente activo por ID.
     */
    public function show($id)
    {
        $ingrediente = IngredienteActivo::findOrFail($id);
        return response()->json($ingrediente);
    }

    /**
     * Actualizar un ingrediente activo.
     */
    public function update(Request $request, $id)
    {
        $ingrediente = IngredienteActivo::findOrFail($id);

        $validated = $request->validate([
            'ingrediente_activo_nombre' => 'required|string|max:255|unique:ingredienteactivo,ingrediente_activo_nombre,' . $ingrediente->ingrediente_activo_id . ',ingrediente_activo_id',
            'ingrediente_activo_detalle' => 'nullable|string',
        ]);

        $ingrediente->update([
            'ingrediente_activo_nombre' => $validated['ingrediente_activo_nombre'],
            'ingrediente_activo_detalle' => $validated['ingrediente_activo_detalle'] ?? null,
        ]);

        return response()->json(['message' => 'Ingrediente activo actualizado', 'ingrediente' => $ingrediente]);
    }

    /**
     * Soft delete lógico (cambiar estado).
     */
    public function destroy($id)
    {
        $ingrediente = IngredienteActivo::findOrFail($id);

        // Alternar entre activo/inactivo
        $ingrediente->ingrediente_activo_estado = $ingrediente->ingrediente_activo_estado ? 0 : 1;
        $ingrediente->save();

        $mensaje = $ingrediente->ingrediente_activo_estado ? 'Ingrediente activo activado' : 'Ingrediente activo desactivado';

        return response()->json(['message' => $mensaje, 'ingrediente' => $ingrediente]);
    }
}
