<?php

namespace App\Http\Controllers;

use App\Models\Tecnico;
use App\Models\TecnicoCategoria;
use Illuminate\Http\Request;

class TecnicoController extends Controller
{
    /**
     * Muestra la vista del listado de técnicos.
     */
    public function viewIndex()
    {
        return view('technical.index');
    }

    /**
     * Listar técnicos (opcionalmente filtrados por estado).
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // 1: activos, 0: inactivos, 'all': todos

        $query = Tecnico::with('categoria');

        if ($estado === '1') {
            $query->where('tecnico_estado', 1);
        } elseif ($estado === '0') {
            $query->where('tecnico_estado', 0);
        }

        $tecnicos = $query->orderByDesc('tecnico_id')->get();

        return response()->json($tecnicos);
    }

    /**
     * Crear nuevo técnico.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tecnido_cedula'    => 'required|string|max:20',
            'tecnico_nombre'    => 'required|string|max:100',
            'tecnico_apellido'  => 'required|string|max:100',
            'tecnico_email'     => 'required|email|max:255|unique:tecnico,tecnico_email',
            'tecnico_telefono'  => 'nullable|string|max:20',
            'categoria_id'      => 'required|exists:tecnico_categoria,tecnico_categoria_id',
            'tecnico_senescyt'  => 'nullable|string|max:255',
        ]);

        $validated['tecnico_estado'] = 1;

        $tecnico = Tecnico::create($validated);

        return response()->json([
            'message' => 'Técnico creado exitosamente.',
            'tecnico' => $tecnico
        ]);
    }

    /**
     * Mostrar un técnico específico.
     */
    public function show($id)
    {
        $tecnico = Tecnico::with('categoria')->findOrFail($id);
        return response()->json($tecnico);
    }

    /**
     * Actualizar un técnico.
     */
    public function update(Request $request, $id)
    {
        $tecnico = Tecnico::findOrFail($id);

        $validated = $request->validate([
            'tecnido_cedula'    => 'required|string|max:20',
            'tecnico_nombre'    => 'required|string|max:100',
            'tecnico_apellido'  => 'required|string|max:100',
            'tecnico_email'     => 'required|email|max:255|unique:tecnico,tecnico_email,' . $id . ',tecnico_id',
            'tecnico_telefono'  => 'nullable|string|max:20',
            'categoria_id'      => 'required|exists:tecnico_categoria,tecnico_categoria_id',
            'tecnico_senescyt'  => 'nullable|string|max:255',
        ]);

        $tecnico->update($validated);

        return response()->json([
            'message' => 'Técnico actualizado exitosamente.',
            'tecnico' => $tecnico
        ]);
    }

    /**
     * Cambiar estado del técnico (activar/desactivar).
     */
    public function destroy($id)
    {
        $tecnico = Tecnico::findOrFail($id);
        $tecnico->tecnico_estado = $tecnico->tecnico_estado ? 0 : 1;
        $tecnico->save();

        $mensaje = $tecnico->tecnico_estado ? 'Técnico activado' : 'Técnico desactivado';

        return response()->json([
            'message' => $mensaje,
            'tecnico' => $tecnico
        ]);
    }

    /**
     * Eliminar permanentemente (opcional).
     */
    public function forceDelete($id)
    {
        $tecnico = Tecnico::withTrashed()->findOrFail($id);
        $tecnico->forceDelete();

        return response()->json(['message' => 'Técnico eliminado permanentemente.']);
    }
}
