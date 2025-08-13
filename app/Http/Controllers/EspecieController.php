<?php

namespace App\Http\Controllers;

use App\Models\Especie;
use Illuminate\Http\Request;

class EspecieController extends Controller
{
    /**
     * Vista principal (opcional)
     */
    public function viewIndex()
    {
        return view('animal.specie'); // Asegúrate de tener esta vista creada
    }

    /**
     * Listar especies filtrando por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto solo activos

        $query = Especie::query();

        if ($estado === '1') {
            $query->where('especie_estado', 1);
        } elseif ($estado === '0') {
            $query->where('especie_estado', 0);
        }

        $especies = $query->orderBy('especie_id', 'desc')->get();

        return response()->json($especies);
    }

    /**
     * Guardar una nueva especie.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'especie_nombre' => 'required|string|max:255|unique:especie,especie_nombre',
            'especie_cientifico' => 'required|string|max:255',
            'especie_detalle' => 'nullable|string',
        ]);

        $especie = Especie::create([
            'especie_nombre' => $validated['especie_nombre'],
            'especie_cientifico' => $validated['especie_cientifico'],
            'especie_detalle' => $validated['especie_detalle'] ?? null,
            'especie_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Especie creada', 'especie' => $especie]);
    }

    /**
     * Mostrar una especie específica por ID.
     */
    public function show($id)
    {
        $especie = Especie::findOrFail($id);
        return response()->json($especie);
    }

    /**
     * Actualizar una especie.
     */
    public function update(Request $request, $id)
    {
        $especie = Especie::findOrFail($id);

        $validated = $request->validate([
            'especie_nombre' => 'required|string|max:255|unique:especie,especie_nombre,' . $especie->especie_id . ',especie_id',
            'especie_cientifico' => 'required|string|max:255',
            'especie_detalle' => 'nullable|string',
        ]);

        $especie->update([
            'especie_nombre' => $validated['especie_nombre'],
            'especie_cientifico' => $validated['especie_cientifico'],
            'especie_detalle' => $validated['especie_detalle'] ?? null,
        ]);

        return response()->json(['message' => 'Especie actualizada', 'especie' => $especie]);
    }

    /**
     * Activar o desactivar especie (soft delete lógico).
     */
    public function destroy($id)
    {
        $especie = Especie::findOrFail($id);

        // Toggle estado (activo/inactivo)
        $especie->especie_estado = $especie->especie_estado ? 0 : 1;
        $especie->save();

        $mensaje = $especie->especie_estado ? 'Especie activada' : 'Especie desactivada';

        return response()->json(['message' => $mensaje, 'especie' => $especie]);
    }
    
}
