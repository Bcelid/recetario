<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Muestra la vista del listado de clientes.
     */
    public function viewIndex()
    {
        return view('store.client');
    }

    /**
     * Listar clientes (opcionalmente filtrados por estado).
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // 1: activos, 0: inactivos, 'all': todos

        $query = Cliente::with('almacen');

        if ($estado === '1') {
            $query->where('cliente_estado', 1);
        } elseif ($estado === '0') {
            $query->where('cliente_estado', 0);
        }

        $clientes = $query->orderByDesc('cliente_id')->get();

        return response()->json($clientes);
    }

    /**
     * Crear nuevo cliente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_cedula'     => 'required|string|max:20|unique:cliente,cliente_cedula',
            'cliente_nombre'     => 'required|string|max:100',
            'cliente_apellido'   => 'required|string|max:100',
            'cliente_direccion'  => 'nullable|string|max:255',
            'cliente_almacen_id' => 'required|exists:almacen,almacen_id',
        ]);

        $validated['cliente_estado'] = 1;

        $cliente = Cliente::create($validated);

        return response()->json([
            'message' => 'Cliente creado exitosamente.',
            'cliente' => $cliente
        ]);
    }

    /**
     * Mostrar un cliente específico.
     */
    public function show($id)
    {
        $cliente = Cliente::with('almacen')->findOrFail($id);
        return response()->json($cliente);
    }

    /**
     * Actualizar un cliente.
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'cliente_cedula'     => 'required|string|max:20|unique:cliente,cliente_cedula,' . $id . ',cliente_id',
            'cliente_nombre'     => 'required|string|max:100',
            'cliente_apellido'   => 'required|string|max:100',
            'cliente_direccion'  => 'nullable|string|max:255',
            'cliente_almacen_id' => 'required|exists:almacen,almacen_id',
        ]);

        $cliente->update($validated);

        return response()->json([
            'message' => 'Cliente actualizado exitosamente.',
            'cliente' => $cliente
        ]);
    }

    /**
     * Cambiar estado del cliente (activar/desactivar).
     */
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->cliente_estado = $cliente->cliente_estado ? 0 : 1;
        $cliente->save();

        $mensaje = $cliente->cliente_estado ? 'Cliente activado' : 'Cliente desactivado';

        return response()->json([
            'message' => $mensaje,
            'cliente' => $cliente
        ]);
    }

    /**
     * Eliminar permanentemente (opcional).
     */
    public function forceDelete($id)
    {
        $cliente = Cliente::withTrashed()->findOrFail($id);
        $cliente->forceDelete();

        return response()->json(['message' => 'Cliente eliminado permanentemente.']);
    }
}
