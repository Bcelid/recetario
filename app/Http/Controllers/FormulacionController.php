<?php

namespace App\Http\Controllers;

use App\Models\Formulacion;
use Illuminate\Http\Request;

class FormulacionController extends Controller
{
    /**
     * Vista principal (opcional)
     */
    public function viewIndex()
    {
        return view('settings.formulation'); // Cambia según la ruta de tu vista
    }

    /**
     * Listar formulaciones filtrando por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto activos

        $query = Formulacion::query();

        if ($estado === '1') {
            $query->where('formulacion_estado', 1);
        } elseif ($estado === '0') {
            $query->where('formulacion_estado', 0);
        }

        $formulaciones = $query->orderBy('formulacion_id', 'desc')->get();

        return response()->json($formulaciones);
    }

    /**
     * Guardar una nueva formulación.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'formulacion_nombre' => 'required|string|max:255|unique:formulacion,formulacion_nombre',
            'formulacion_abreviatura' => 'nullable|string|max:50',
        ]);

        $formulacion = Formulacion::create([
            'formulacion_nombre' => $validated['formulacion_nombre'],
            'formulacion_abreviatura' => $validated['formulacion_abreviatura'] ?? null,
            'formulacion_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Formulación creada', 'formulacion' => $formulacion]);
    }

    /**
     * Mostrar una formulación por ID.
     */
    public function show($id)
    {
        $formulacion = Formulacion::findOrFail($id);
        return response()->json($formulacion);
    }

    /**
     * Actualizar una formulación.
     */
    public function update(Request $request, $id)
    {
        $formulacion = Formulacion::findOrFail($id);

        $validated = $request->validate([
            'formulacion_nombre' => 'required|string|max:255|unique:formulacion,formulacion_nombre,' . $formulacion->formulacion_id . ',formulacion_id',
            'formulacion_abreviatura' => 'nullable|string|max:50',
        ]);

        $formulacion->update([
            'formulacion_nombre' => $validated['formulacion_nombre'],
            'formulacion_abreviatura' => $validated['formulacion_abreviatura'] ?? null,
        ]);

        return response()->json(['message' => 'Formulación actualizada', 'formulacion' => $formulacion]);
    }

    /**
     * Soft delete lógico (cambiar estado).
     */
    public function destroy($id)
    {
        $formulacion = Formulacion::findOrFail($id);

        // Alternar entre activo/inactivo
        $formulacion->formulacion_estado = $formulacion->formulacion_estado ? 0 : 1;
        $formulacion->save();

        $mensaje = $formulacion->formulacion_estado ? 'Formulación activada' : 'Formulación desactivada';

        return response()->json(['message' => $mensaje, 'formulacion' => $formulacion]);
    }
}
