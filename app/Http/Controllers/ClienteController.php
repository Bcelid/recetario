<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as PhpSpreadsheetException;


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
            'cliente_cedula'     => 'required|string|max:20',
            'cliente_nombre'     => 'required|string|max:100',
            'cliente_apellido'   => 'required|string|max:100',
            'cliente_direccion'  => 'nullable|string|max:255',
            'cliente_almacen_id' => 'required|exists:almacen,almacen_id',
        ]);

        // Validar si la combinación de cedula y almacen ya existe
        $exists = Cliente::where('cliente_cedula', $validated['cliente_cedula'])
            ->where('cliente_almacen_id', $validated['cliente_almacen_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este cliente ya está registrado en este almacén.',
            ], 400);
        }

        // Estado por defecto
        $validated['cliente_estado'] = 1;

        // Crear el cliente
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

        // Validar si la combinación de cedula y almacen ya existe (excepto en el cliente actual)
        $exists = Cliente::where('cliente_cedula', $validated['cliente_cedula'])
            ->where('cliente_almacen_id', $validated['cliente_almacen_id'])
            ->where('cliente_id', '!=', $id)  // Excluir el cliente actual
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este cliente ya está registrado en este almacén.',
            ], 400);
        }

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

    public function import(Request $request)
    {
        $request->validate([
            'almacen_id' => 'required|exists:almacen,almacen_id',
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('excel_file');

        $clientesYaRegistrados = [];
        $clientesDuplicados = [];
        $errores = [];

        try {
            // Cargar el archivo Excel
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows) || count($rows) == 1) {
                return response()->json([
                    'message' => 'El archivo está vacío o solo contiene encabezados.',
                ], 400);
            }

            foreach ($rows as $index => $row) {
                if ($index == 0) continue; // Ignorar la fila de encabezados

                // Comprobar si toda la fila está vacía
                if (empty(array_filter($row))) {
                    continue; // Si la fila está vacía, la saltamos
                }

                // Verificar si hay campos vacíos en la fila
                $cedula = $row[0];
                $nombre = $row[1];
                $apellido = $row[2];

                if (empty($cedula) || empty($nombre) || empty($apellido)) {
                    $errores[] = "Fila " . ($index + 1) . ": Faltan campos obligatorios (Cedula, Nombre o Apellido).";
                    continue; // Saltar esta fila
                }

                $clienteData = [
                    'cliente_cedula'    => $cedula,
                    'cliente_nombre'    => $nombre,
                    'cliente_apellido'  => $apellido,
                    'cliente_direccion' => $row[3],  // Este campo es opcional
                    'cliente_estado'    => 1,
                    'cliente_almacen_id' => $request->almacen_id,
                ];

                // Verificar si ya existe un cliente con la misma cédula y en el mismo almacén
                $exists = Cliente::where('cliente_cedula', $clienteData['cliente_cedula'])
                    ->where('cliente_almacen_id', $clienteData['cliente_almacen_id'])
                    ->exists();

                if ($exists) {
                    $clientesDuplicados[] = $clienteData['cliente_nombre'] . ' ' . $clienteData['cliente_apellido'];
                } else {
                    // Crear el cliente si no existe
                    Cliente::create($clienteData);
                    $clientesYaRegistrados[] = $clienteData['cliente_nombre'] . ' ' . $clienteData['cliente_apellido'];
                }
            }


            // Si hay errores, devolver los mensajes correspondientes
            if (!empty($errores)) {
                return response()->json([
                    'message' => 'El archivo tiene algunos errores.',
                    'errores' => $errores,
                ], 400);
            }

            return response()->json([
                'message' => 'Clientes importados correctamente.',
                'clientes_ya_registrados' => $clientesYaRegistrados,
                'clientes_duplicados' => $clientesDuplicados,
            ]);
        } catch (PhpSpreadsheetException $e) {
            return response()->json([
                'message' => 'Error al procesar el archivo Excel.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 400);
        }
    }
}
