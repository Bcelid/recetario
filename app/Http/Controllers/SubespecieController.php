<?php

namespace App\Http\Controllers;

use App\Models\Subespecie;
use Illuminate\Http\Request;

class SubespecieController extends Controller
{
    /**
     * Vista principal
     */
    public function viewIndex()
    {
        return view('animal.subspecie');
    }

    /**
     * Listar subespecies filtrando por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto solo activos

        $query = Subespecie::with('especie');

        if ($estado === '1') {
            $query->where('subespecie_estado', 1);
        } elseif ($estado === '0') {
            $query->where('subespecie_estado', 0);
        }

        $subespecies = $query->orderBy('subespecie_id', 'desc')->get();

        return response()->json($subespecies);
    }

    /**
     * Guardar una nueva subespecie.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subespecie_nombre' => 'required|string|max:255|unique:subespecie,subespecie_nombre',
            'subespecie_cientifico' => 'required|string|max:255',
            'subespecie_detalle' => 'nullable|string',
            'especie_id' => 'required|exists:especie,especie_id',
        ]);

        $subespecie = Subespecie::create([
            'subespecie_nombre' => $validated['subespecie_nombre'],
            'subespecie_cientifico' => $validated['subespecie_cientifico'],
            'subespecie_detalle' => $validated['subespecie_detalle'] ?? null,
            'especie_id' => $validated['especie_id'],
            'subespecie_estado' => 1,
        ]);

        return response()->json(['message' => 'Subespecie creada', 'subespecie' => $subespecie]);
    }

    /**
     * Mostrar una subespecie específica.
     */
    public function show($id)
    {
        $subespecie = Subespecie::findOrFail($id);
        return response()->json($subespecie);
    }

    /**
     * Actualizar subespecie.
     */
    public function update(Request $request, $id)
    {
        $subespecie = Subespecie::findOrFail($id);

        $validated = $request->validate([
            'subespecie_nombre' => 'required|string|max:255|unique:subespecie,subespecie_nombre,' . $subespecie->subespecie_id . ',subespecie_id',
            'subespecie_cientifico' => 'required|string|max:255',
            'subespecie_detalle' => 'nullable|string',
            'especie_id' => 'required|exists:especie,especie_id',
        ]);

        $subespecie->update([
            'subespecie_nombre' => $validated['subespecie_nombre'],
            'subespecie_cientifico' => $validated['subespecie_cientifico'],
            'subespecie_detalle' => $validated['subespecie_detalle'] ?? null,
            'especie_id' => $validated['especie_id'],
        ]);

        return response()->json(['message' => 'Subespecie actualizada', 'subespecie' => $subespecie]);
    }

    /**
     * Activar o desactivar subespecie (soft delete lógico).
     */
    public function destroy($id)
    {
        $subespecie = Subespecie::findOrFail($id);

        $subespecie->subespecie_estado = $subespecie->subespecie_estado ? 0 : 1;
        $subespecie->save();

        $mensaje = $subespecie->subespecie_estado ? 'Subespecie activada' : 'Subespecie desactivada';

        return response()->json(['message' => $mensaje, 'subespecie' => $subespecie]);
    }

    public function listActive()
    {
        $especies = \App\Models\Especie::where('especie_estado', 1)
            ->orderBy('especie_nombre')
            ->get();

        return response()->json($especies);
    }

    /**
     * Listar subespecies activas filtradas por especie_id.
     */
    public function listByEspecie($especie_id)
    {
        $subespecies = Subespecie::where('especie_id', $especie_id)
            ->where('subespecie_estado', 1)
            ->orderBy('subespecie_nombre')
            ->get();

        return response()->json($subespecies);
    }
}
