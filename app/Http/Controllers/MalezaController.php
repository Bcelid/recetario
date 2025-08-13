<?php

namespace App\Http\Controllers;

use App\Models\Maleza;
use Illuminate\Http\Request;

class MalezaController extends Controller
{
    /**
     * Vista principal (opcional)
     */
    public function viewIndex()
    {
        return view('crop.plague'); // Asegúrate de tener esta vista en resources/views/weed/index.blade.php
    }

    /**
     * Listar malezas filtrando por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto solo activos

        $query = Maleza::query();

        if ($estado === '1') {
            $query->where('maleza_estado', 1);
        } elseif ($estado === '0') {
            $query->where('maleza_estado', 0);
        }

        $malezas = $query->orderBy('maleza_id', 'desc')->get();

        return response()->json($malezas);
    }

    /**
     * Guardar una nueva maleza.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'maleza_nombre' => 'required|string|max:255|unique:maleza,maleza_nombre',
            'maleza_cientifico' => 'required|string|max:255',
            'maleza_detalle' => 'nullable|string',
        ]);

        $maleza = Maleza::create([
            'maleza_nombre' => $validated['maleza_nombre'],
            'maleza_cientifico' => $validated['maleza_cientifico'],
            'maleza_detalle' => $validated['maleza_detalle'] ?? null,
            'maleza_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Maleza creada', 'maleza' => $maleza]);
    }

    /**
     * Mostrar una maleza específica por ID.
     */
    public function show($id)
    {
        $maleza = Maleza::findOrFail($id);
        return response()->json($maleza);
    }

    /**
     * Actualizar una maleza.
     */
    public function update(Request $request, $id)
    {
        $maleza = Maleza::findOrFail($id);

        $validated = $request->validate([
            'maleza_nombre' => 'required|string|max:255|unique:maleza,maleza_nombre,' . $maleza->maleza_id . ',maleza_id',
            'maleza_cientifico' => 'required|string|max:255',
            'maleza_detalle' => 'nullable|string',
        ]);

        $maleza->update([
            'maleza_nombre' => $validated['maleza_nombre'],
            'maleza_cientifico' => $validated['maleza_cientifico'],
            'maleza_detalle' => $validated['maleza_detalle'] ?? null,
        ]);

        return response()->json(['message' => 'Maleza actualizada', 'maleza' => $maleza]);
    }

    /**
     * Soft delete lógico (cambiar estado).
     */
    public function destroy($id)
    {
        $maleza = Maleza::findOrFail($id);

        // Toggle estado (activo/inactivo)
        $maleza->maleza_estado = $maleza->maleza_estado ? 0 : 1;
        $maleza->save();

        $mensaje = $maleza->maleza_estado ? 'Maleza activada' : 'Maleza desactivada';

        return response()->json(['message' => $mensaje, 'maleza' => $maleza]);
    }
}
