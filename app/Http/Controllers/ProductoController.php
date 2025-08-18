<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\ProductoIngrediente;
use App\Models\Dosificacion;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function viewIndex()
    {
        return view('product.index'); // Ajusta a la ruta de tu vista
    }
    public function viewCreate()
    {
        return view('product.product'); // Ajusta a la ruta de tu vista para crear producto
    }

    public function index(Request $request)
    {
        $estado = $request->get('estado', 'all');
        $tipo = $request->get('tipo', 'all'); // Nuevo filtro

        $query = Producto::with(['unidadMedida', 'formulacion']);

        if ($estado !== 'all') {
            $query->where('producto_estado', $estado);
        }

        if ($tipo !== 'all') {
            $query->where('producto_tipo', $tipo);
        }

        $productos = $query->get();

        $productos = $productos->map(function ($producto) {
            return [
                'producto_id' => $producto->producto_id,
                'producto_nombre' => $producto->producto_nombre,
                'producto_concentracion' => $producto->producto_concentracion,
                'presentacion_unidad' => $producto->producto_presentacion . ' ' . ($producto->unidadMedida->unidad_medida_detalle ?? ''),
                'formulacion_abreviatura' => $producto->formulacion->formulacion_abreviatura ?? '',
                'producto_estado' => $producto->producto_estado,
                'producto_tipo' => $producto->producto_tipo,
            ];
        });

        return response()->json($productos);
    }



    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // 1. Crear producto
            $producto = Producto::create([
                'producto_nombre'         => $request->nombre_producto,
                'producto_concentracion'  => $request->concentracion,
                'producto_presentacion'   => $request->presentacion,
                'unidad_medida_id'        => $request->unidad_medida_id,
                'formulacion_id'          => $request->formulacion_id,
                'producto_estado'         => 1,
                'producto_diagnostico'    => $request->diagnostico,
                'producto_unidad_en_envase' => $request->cantidad_envase,
                'producto_tipo'           => $request->tipo_producto,
            ]);

            // 2. Ingredientes activos (array esperado)
            foreach ($request->ingredientes as $ing) {
                ProductoIngrediente::create([
                    'producto_id'         => $producto->producto_id,
                    'ingrediente_activo_id' => $ing['ingrediente_id'],
                    'cantidad'            => $ing['porcentaje'],
                    'unidad_medida_id'    => $ing['unidad_id'],
                ]);
            }

            // 3. Dosificaciones
            foreach ($request->dosificaciones as $dosis) {
                Dosificacion::create([
                    'producto_id'                    => $producto->producto_id,
                    'cultivo_id'                    => $dosis['cultivo_id'] ?? null,
                    'maleza_id'                     => $dosis['maleza_id'] ?? null,
                    'subespecie_id'                 => $dosis['subespecie_id'] ?? null,
                    'dosis'                         => $dosis['dosis'] ?? null,
                    'unidad_medida_dosificacion_id' => $dosis['unidad_dosificacion_id'] ?? null,
                    'dosificacion_aplicacion'       => $dosis['aplicacion'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Producto registrado correctamente'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar el producto', 'detalle' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id); // ✅ Producto, no Cliente
        $producto->producto_estado = $producto->producto_estado ? 0 : 1;
        $producto->save();

        $mensaje = $producto->producto_estado ? 'Producto activado' : 'Producto desactivado';

        return response()->json([
            'message' => $mensaje,
            'producto' => $producto
        ]);
    }

    public function show($id)
    {
        $producto = Producto::with([
            'unidadMedida',
            'formulacion',
            'ingredientes.ingredienteActivo',
            'ingredientes.unidadMedida',
            'dosificaciones.cultivo',
            'dosificaciones.maleza',
            'dosificaciones.subespecie.especie',
            'dosificaciones.unidadMedidaDosificacion',
        ])->findOrFail($id);

        return view('product.show', compact('producto'));
    }


    public function edit($id)
    {
        $producto = Producto::with([
            'unidadMedida',
            'formulacion',
            'ingredientes.ingredienteActivo',
            'ingredientes.unidadMedida',
            'dosificaciones.cultivo',
            'dosificaciones.maleza',
            'dosificaciones.subespecie.especie',
            'dosificaciones.unidadMedidaDosificacion',
        ])->findOrFail($id);

        return view('product.edit', compact('producto'));
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($id);

            // 1. Actualizar producto
            $producto->update([
                'producto_nombre'         => $request->nombre_producto,
                'producto_concentracion'  => $request->concentracion,
                'producto_presentacion'   => $request->presentacion,
                'unidad_medida_id'        => $request->unidad_medida_id,
                'formulacion_id'          => $request->formulacion_id,
                'producto_diagnostico'    => $request->diagnostico,
                'producto_unidad_en_envase' => $request->cantidad_envase,
                'producto_tipo'           => $request->tipo_producto,
            ]);

            // 2. Borrar y recrear ingredientes
            $producto->ingredientes()->delete();
            foreach ($request->ingredientes as $ing) {
                ProductoIngrediente::create([
                    'producto_id'           => $producto->producto_id,
                    'ingrediente_activo_id' => $ing['ingrediente_id'],
                    'cantidad'              => $ing['porcentaje'],
                    'unidad_medida_id'      => $ing['unidad_id'],
                ]);
            }

            // 3. Borrar y recrear dosificaciones
            $producto->dosificaciones()->delete();
            foreach ($request->dosificaciones as $dosis) {
                Dosificacion::create([
                    'producto_id'                    => $producto->producto_id,
                    'cultivo_id'                    => $dosis['cultivo_id'] ?? null,
                    'maleza_id'                     => $dosis['maleza_id'] ?? null,
                    'subespecie_id'                 => $dosis['subespecie_id'] ?? null,
                    'dosis'                         => $dosis['dosis'] ?? null,
                    'unidad_medida_dosificacion_id' => $dosis['unidad_dosificacion_id'] ?? null,
                    'dosificacion_aplicacion' => $dosis['aplicacion'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Producto actualizado correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el producto', 'detalle' => $e->getMessage()], 500);
        }
    }
}
