<?php

namespace App\Http\Controllers;

use App\Models\RecetaLote;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Query base para los lotes
        $query = RecetaLote::query();

        // Filtros aplicables a todos los conteos
        if ($request->filled('tipo_lote')) {
            $query->where('receta_tipo', $request->tipo_lote);
        }

        if ($request->filled('fecha_min')) {
            $query->whereDate('fecha_creacion', '>=', $request->fecha_min);
        }

        if ($request->filled('fecha_max')) {
            $query->whereDate('fecha_creacion', '<=', $request->fecha_max);
        }
        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        // Clonar la query base para contar enviados y no enviados sin perder filtros
        $enviadosCount = (clone $query)->where('receta_lote_enviado', true)->count();
        $noEnviadosCount = (clone $query)->where('receta_lote_enviado', false)->count();

        // Aplicar filtro de estado solo para los datos paginados (opcional)
        if ($request->filled('estado')) {
            if ($request->estado == 'enviados') {
                $query->where('receta_lote_enviado', true);
            } elseif ($request->estado == 'no_enviados') {
                $query->where('receta_lote_enviado', false);
            }
        }

        // Obtener resultados paginados
        $lotes = $query->paginate(10);

        return view('dashboard', compact('enviadosCount', 'noEnviadosCount', 'lotes'));
    }


    public function getData(Request $request)
    {
        $query = RecetaLote::with(['tecnico', 'almacen']);

        if ($request->filled('tipo_lote')) {
            $query->where('receta_tipo', $request->tipo_lote);
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'enviados') {
                $query->where('receta_lote_enviado', true);
            } elseif ($request->estado === 'no_enviados') {
                $query->where('receta_lote_enviado', false);
            }
        }

        if ($request->filled('fecha_min')) {
            $query->whereDate('fecha_creacion', '>=', $request->fecha_min);
        }

        if ($request->filled('fecha_max')) {
            $query->whereDate('fecha_creacion', '<=', $request->fecha_max);
        }

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        return DataTables::eloquent($query)
            ->addColumn('tecnico', fn($lote) => $lote->tecnico->name ?? 'N/A')
            ->addColumn('almacen', fn($lote) => $lote->almacen->almacen_nombre ?? 'N/A')
            ->editColumn('receta_tipo', fn($lote) => $lote->receta_tipo == 0 ? 'Agrícola' : 'Veterinario')
            ->addColumn('estado_envio', fn($lote) => $lote->receta_lote_enviado ? 'Enviado' : 'No enviado')
            ->make(true);
    }

    public function getCounts(Request $request)
    {
        $tipo = $request->input('tipo_lote');
        $estado = $request->input('estado');
        $fechaMin = $request->input('fecha_min');
        $fechaMax = $request->input('fecha_max');

        $query = RecetaLote::query();

        if ($tipo !== null && $tipo !== '') {
            $query->where('receta_tipo', $tipo);
        }

        if ($fechaMin) {
            $query->whereDate('created_at', '>=', $fechaMin);
        }

        if ($fechaMax) {
            $query->whereDate('created_at', '<=', $fechaMax);
        }

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        $enviados = (clone $query)->whereNotNull('receta_lote_fecha_envio')->count();
        $noEnviados = (clone $query)->whereNull('receta_lote_fecha_envio')->count();

        return response()->json([
            'enviados' => $enviados,
            'no_enviados' => $noEnviados,
        ]);
    }
}
