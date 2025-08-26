<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\PropietarioAlmacen;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    /**
     * Muestra la vista del listado de almacenes.
     */
    public function viewIndex()
    {
        return view('store.index');
    }

    /**
     * Listar almacenes (opcionalmente filtrados por estado).
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // 1: activos, 0: inactivos, 'all': todos

        $query = Almacen::with('propietario');

        if ($estado === '1') {
            $query->where('almacen_estado', 1);
        } elseif ($estado === '0') {
            $query->where('almacen_estado', 0);
        }

        $almacenes = $query->orderByDesc('almacen_id')->get();

        return response()->json($almacenes);
    }

    /**
     * Crear nuevo almacén.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'almacen_propietario_id' => 'required|exists:propietario_almacen,propietario_almacen_id',
            'almacen_direccion'      => 'nullable|string|max:255',
            'almacen_telefono'       => 'nullable|string|max:20',
            'almacen_correo'         => 'nullable|email|max:255',
            'almacen_nombre'         => 'required|string|max:255',
            'almacen_logo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $validated['almacen_estado'] = 1;

        $almacen = Almacen::create($validated);

        // Guardar logo si existe
        if ($request->hasFile('almacen_logo')) {
            $logoPath = $request->file('almacen_logo')->storeAs(
                'app/logo_almacen',
                $almacen->almacen_id . '.' . $request->file('almacen_logo')->extension(),
                'public'
            );

            $almacen->update([
                'almacen_logo' => $logoPath
            ]);
        }

        return response()->json([
            'message' => 'Almacén creado exitosamente.',
            'almacen' => $almacen
        ]);
    }


    /**
     * Mostrar un almacén específico.
     */
    public function show($id)
    {
        $almacen = Almacen::with('propietario')->findOrFail($id);
        return response()->json($almacen);
    }

    /**
     * Actualizar un almacén.
     */
    public function update(Request $request, $id)
    {
        $almacen = Almacen::findOrFail($id);

        $validated = $request->validate([
            'almacen_propietario_id' => 'required|exists:propietario_almacen,propietario_almacen_id',
            'almacen_direccion'      => 'nullable|string|max:255',
            'almacen_telefono'       => 'nullable|string|max:20',
            'almacen_correo'         => 'nullable|email|max:255',
            'almacen_nombre'         => 'required|string|max:255',
            'almacen_logo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $almacen->update($validated);

        // Subir nuevo logo si viene
        if ($request->hasFile('almacen_logo')) {
            $logoPath = $request->file('almacen_logo')->storeAs(
                'app/logo_almacen',
                $almacen->almacen_id . '.' . $request->file('almacen_logo')->extension(),
                'public'
            );

            $almacen->update([
                'almacen_logo' => $logoPath
            ]);
        }

        return response()->json([
            'message' => 'Almacén actualizado exitosamente.',
            'almacen' => $almacen
        ]);
    }

    /**
     * Cambiar estado del almacén (activar/desactivar).
     */
    public function destroy($id)
    {
        $almacen = Almacen::findOrFail($id);
        $almacen->almacen_estado = $almacen->almacen_estado ? 0 : 1;
        $almacen->save();

        $mensaje = $almacen->almacen_estado ? 'Almacén activado' : 'Almacén desactivado';

        return response()->json([
            'message' => $mensaje,
            'almacen' => $almacen
        ]);
    }

    /**
     * Eliminar permanentemente (opcional).
     */
    public function forceDelete($id)
    {
        $almacen = Almacen::withTrashed()->findOrFail($id);
        $almacen->forceDelete();

        return response()->json(['message' => 'Almacén eliminado permanentemente.']);
    }
    public function search(Request $request)
    {
        $query = $request->input('q');

        $propietarios = PropietarioAlmacen::where('deleted_at', null)
            ->when($query, function ($qbuilder) use ($query) {
                $qbuilder->where(function ($sub) use ($query) {
                    $sub->where('propietario_almacen_nombre', 'LIKE', "%{$query}%")
                        ->orWhere('propietario_almacen_apellido', 'LIKE', "%{$query}%");
                });
            })
            ->orderBy('propietario_almacen_nombre', 'asc')
            ->limit(20) // Limita resultados para evitar cargar demasiado
            ->get();

        $results = $propietarios->map(function ($prop) {
            return [
                'id' => $prop->propietario_almacen_id,
                'text' => $prop->propietario_almacen_nombre . ' ' . $prop->propietario_almacen_apellido
            ];
        });

        return response()->json(['results' => $results]);
    }
}
