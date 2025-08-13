<?php

namespace App\Http\Controllers;

use App\Models\Cultivo;
use Illuminate\Http\Request;

class CultivoController extends Controller
{
    /**
     * Vista principal (opcional)
     */
    public function viewIndex()
    {
        return view('crop.plant'); // Asegúrate de tener esta vista
    }

    /**
     * Listar cultivos filtrando por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto solo activos

        $query = Cultivo::query();

        if ($estado === '1') {
            $query->where('cultivo_estado', 1);
        } elseif ($estado === '0') {
            $query->where('cultivo_estado', 0);
        }

        $cultivos = $query->orderBy('cultivo_id', 'desc')->get();

        return response()->json($cultivos);
    }

    /**
     * Guardar un nuevo cultivo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cultivo_nombre' => 'required|string|max:255|unique:cultivo,cultivo_nombre',
            'cultivo_cientifico' => 'required|string|max:255',
            'cultivo_detalle' => 'nullable|string',
        ]);

        $cultivo = Cultivo::create([
            'cultivo_nombre' => $validated['cultivo_nombre'],
            'cultivo_cientifico' => $validated['cultivo_cientifico'],
            'cultivo_detalle' => $validated['cultivo_detalle'] ?? null,
            'cultivo_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Cultivo creado', 'cultivo' => $cultivo]);
    }

    /**
     * Mostrar un cultivo específico por ID.
     */
    public function show($id)
    {
        $cultivo = Cultivo::findOrFail($id);
        return response()->json($cultivo);
    }

    /**
     * Actualizar un cultivo.
     */
    public function update(Request $request, $id)
    {
        $cultivo = Cultivo::findOrFail($id);

        $validated = $request->validate([
            'cultivo_nombre' => 'required|string|max:255|unique:cultivo,cultivo_nombre,' . $cultivo->cultivo_id . ',cultivo_id',
            'cultivo_cientifico' => 'required|string|max:255',
            'cultivo_detalle' => 'nullable|string',
        ]);

        $cultivo->update([
            'cultivo_nombre' => $validated['cultivo_nombre'],
            'cultivo_cientifico' => $validated['cultivo_cientifico'],
            'cultivo_detalle' => $validated['cultivo_detalle'] ?? null,
        ]);

        return response()->json(['message' => 'Cultivo actualizado', 'cultivo' => $cultivo]);
    }

    /**
     * Soft delete lógico (cambiar estado).
     */
    public function destroy($id)
    {
        $cultivo = Cultivo::findOrFail($id);

        // Toggle estado (activo/inactivo)
        $cultivo->cultivo_estado = $cultivo->cultivo_estado ? 0 : 1;
        $cultivo->save();

        $mensaje = $cultivo->cultivo_estado ? 'Cultivo activado' : 'Cultivo desactivado';

        return response()->json(['message' => $mensaje, 'cultivo' => $cultivo]);
    }
}
