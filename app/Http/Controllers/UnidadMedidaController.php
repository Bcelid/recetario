<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    /**
     * Vista principal (opcional)
     */
    public function viewIndex()
    {
        return view('settings.measure'); // Cambia según la ruta de tu vista
    }

    /**
     * Listar unidades de medida filtrando por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto activos

        $query = UnidadMedida::query();

        if ($estado === '1') {
            $query->where('unidad_medida_estado', 1);
        } elseif ($estado === '0') {
            $query->where('unidad_medida_estado', 0);
        }

        $unidades = $query->orderBy('unidad_medida_id', 'desc')->get();

        return response()->json($unidades);
    }

    /**
     * Guardar una nueva unidad de medida.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unidad_medida_detalle' => 'required|string|max:255|unique:unidad_medida,unidad_medida_detalle',
        ]);

        $unidad = UnidadMedida::create([
            'unidad_medida_detalle' => $validated['unidad_medida_detalle'],
            'unidad_medida_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Unidad de medida creada', 'unidad' => $unidad]);
    }

    /**
     * Mostrar una unidad de medida por ID.
     */
    public function show($id)
    {
        $unidad = UnidadMedida::findOrFail($id);
        return response()->json($unidad);
    }

    /**
     * Actualizar una unidad de medida.
     */
    public function update(Request $request, $id)
    {
        $unidad = UnidadMedida::findOrFail($id);

        $validated = $request->validate([
            'unidad_medida_detalle' => 'required|string|max:255|unique:unidad_medida,unidad_medida_detalle,' . $unidad->unidad_medida_id . ',unidad_medida_id',
        ]);

        $unidad->update([
            'unidad_medida_detalle' => $validated['unidad_medida_detalle'],
        ]);

        return response()->json(['message' => 'Unidad de medida actualizada', 'unidad' => $unidad]);
    }

    /**
     * Soft delete lógico (cambiar estado).
     */
    public function destroy($id)
    {
        $unidad = UnidadMedida::findOrFail($id);

        // Alternar entre activo/inactivo
        $unidad->unidad_medida_estado = $unidad->unidad_medida_estado ? 0 : 1;
        $unidad->save();

        $mensaje = $unidad->unidad_medida_estado ? 'Unidad de medida activada' : 'Unidad de medida desactivada';

        return response()->json(['message' => $mensaje, 'unidad' => $unidad]);
    }
}
