<?php

namespace App\Http\Controllers;

use App\Models\PropietarioAlmacen;
use Illuminate\Http\Request;

class PropietarioAlmacenController extends Controller
{
    public function viewIndex()
    {
        return view('store.storeboss'); // Ajusta a la ruta de tu vista
    }

    /**
     * Listar propietarios (puede filtrar por estado).
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // por defecto solo activos

        $query = PropietarioAlmacen::query();

        if ($estado === '1') {
            $query->where('propietario_almacen_estado', 1);
        } elseif ($estado === '0') {
            $query->where('propietario_almacen_estado', 0);
        }

        $propietarios = $query->orderBy('propietario_almacen_id', 'desc')->get();

        return response()->json($propietarios);
    }

    /**
     * Guardar nuevo propietario.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'propietario_almacen_nombre' => 'required|string|max:255',
            'propietario_almacen_apellido' => 'required|string|max:255',
            'propietario_almacen_direccion' => 'required|string|max:255',
        ]);

        $propietario = PropietarioAlmacen::create([
            'propietario_almacen_nombre' => $validated['propietario_almacen_nombre'],
            'propietario_almacen_apellido' => $validated['propietario_almacen_apellido'],
            'propietario_almacen_direccion' => $validated['propietario_almacen_direccion'],
            'propietario_almacen_estado' => 1, // activo por defecto
        ]);

        return response()->json(['message' => 'Propietario creado', 'propietario' => $propietario]);
    }

    /**
     * Mostrar propietario por id.
     */
    public function show($id)
    {
        $propietario = PropietarioAlmacen::findOrFail($id);
        return response()->json($propietario);
    }

    /**
     * Actualizar propietario.
     */
    public function update(Request $request, $id)
    {
        $propietario = PropietarioAlmacen::findOrFail($id);

        $validated = $request->validate([
            'propietario_almacen_nombre' => 'required|string|max:255',
            'propietario_almacen_apellido' => 'required|string|max:255',
            'propietario_almacen_direccion' => 'required|string|max:255',
        ]);

        $propietario->update($validated);

        return response()->json(['message' => 'Propietario actualizado', 'propietario' => $propietario]);
    }

    /**
     * Activar o desactivar propietario.
     */
    public function destroy($id)
    {
        $propietario = PropietarioAlmacen::findOrFail($id);

        $propietario->propietario_almacen_estado = $propietario->propietario_almacen_estado ? 0 : 1;
        $propietario->save();

        $mensaje = $propietario->propietario_almacen_estado ? 'Propietario activado' : 'Propietario desactivado';

        return response()->json(['message' => $mensaje, 'propietario' => $propietario]);
    }
}
