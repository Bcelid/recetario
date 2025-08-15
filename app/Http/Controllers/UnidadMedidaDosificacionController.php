<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedidaDosificacion;
use Illuminate\Http\Request;

class UnidadMedidaDosificacionController extends Controller
{
    /**
     * Vista principal (opcional)
     */
    public function viewIndex()
    {
        return view('settings.dosageunit'); // Cambia según la ruta de tu vista
    }

    /**
     * Listar unidades de medida de dosificación filtrando por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto activos

        $query = UnidadMedidaDosificacion::query();

        if ($estado === '1') {
            $query->where('unidad_medida_dosificacion_estado', 1);
        } elseif ($estado === '0') {
            $query->where('unidad_medida_dosificacion_estado', 0);
        }

        $unidades = $query->orderBy('unidad_medida_dosificacion_id', 'desc')->get();

        return response()->json($unidades);
    }

    /**
     * Guardar una nueva unidad de medida de dosificación.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unidad_medida_dosificacion_representacion' => 'required|string|max:255|unique:unidad_medida_dosificacion,unidad_medida_dosificacion_representacion',
            'unidad_medida_dosificacion_detalle' => 'nullable|string|max:255',
        ]);

        $unidad = UnidadMedidaDosificacion::create([
            'unidad_medida_dosificacion_representacion' => $validated['unidad_medida_dosificacion_representacion'],
            'unidad_medida_dosificacion_detalle' => $validated['unidad_medida_dosificacion_detalle'] ?? null,
            'unidad_medida_dosificacion_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Unidad de medida de dosificación creada', 'unidad' => $unidad]);
    }

    /**
     * Mostrar una unidad de medida de dosificación por ID.
     */
    public function show($id)
    {
        $unidad = UnidadMedidaDosificacion::findOrFail($id);
        return response()->json($unidad);
    }

    /**
     * Actualizar una unidad de medida de dosificación.
     */
    public function update(Request $request, $id)
    {
        $unidad = UnidadMedidaDosificacion::findOrFail($id);

        $validated = $request->validate([
            'unidad_medida_dosificacion_representacion' => 'required|string|max:255|unique:unidad_medida_dosificacion,unidad_medida_dosificacion_representacion,' . $unidad->unidad_medida_dosificacion_id . ',unidad_medida_dosificacion_id',
            'unidad_medida_dosificacion_detalle' => 'nullable|string|max:255',
        ]);

        $unidad->update([
            'unidad_medida_dosificacion_representacion' => $validated['unidad_medida_dosificacion_representacion'],
            'unidad_medida_dosificacion_detalle' => $validated['unidad_medida_dosificacion_detalle'] ?? null,
        ]);

        return response()->json(['message' => 'Unidad de medida de dosificación actualizada', 'unidad' => $unidad]);
    }

    /**
     * Soft delete lógico (cambiar estado).
     */
    public function destroy($id)
    {
        $unidad = UnidadMedidaDosificacion::findOrFail($id);

        // Alternar entre activo/inactivo
        $unidad->unidad_medida_dosificacion_estado = $unidad->unidad_medida_dosificacion_estado ? 0 : 1;
        $unidad->save();

        $mensaje = $unidad->unidad_medida_dosificacion_estado ? 'Unidad de medida de dosificación activada' : 'Unidad de medida de dosificación desactivada';

        return response()->json(['message' => $mensaje, 'unidad' => $unidad]);
    }
}
