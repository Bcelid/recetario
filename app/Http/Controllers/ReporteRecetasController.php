<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Cliente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteRecetasController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $this->validarFiltros($request);
        $datos = $this->obtenerDatosReporte($filtros);

        $almacenes = Almacen::query()
            ->where('almacen_estado', 1)
            ->orderBy('almacen_nombre')
            ->get(['almacen_id', 'almacen_nombre']);

        $clientes = collect();
        if (!empty($filtros['almacen_id'])) {
            $clientes = Cliente::query()
                ->where('cliente_almacen_id', $filtros['almacen_id'])
                ->where('cliente_estado', 1)
                ->orderBy('cliente_nombre')
                ->orderBy('cliente_apellido')
                ->get(['cliente_id', 'cliente_nombre', 'cliente_apellido', 'cliente_cedula']);
        } elseif (!empty($filtros['cliente_id'])) {
            $clientes = Cliente::query()
                ->whereKey($filtros['cliente_id'])
                ->get(['cliente_id', 'cliente_nombre', 'cliente_apellido', 'cliente_cedula']);
        }

        return view('reports.prescriptions', array_merge($datos, compact(
            'almacenes',
            'clientes',
            'filtros'
        )));
    }

    public function pdf(Request $request): Response
    {
        $filtros = $this->validarFiltros($request);
        $datos = $this->obtenerDatosReporte($filtros);
        $etiquetas = $this->etiquetasFiltros($filtros);
        $nombre = "reporte-productos-{$filtros['fecha_desde']}-{$filtros['fecha_hasta']}.pdf";

        return Pdf::loadView('reports.prescriptions-pdf', array_merge(
            $datos,
            compact('etiquetas', 'filtros')
        ))
            ->setPaper('a4', 'landscape')
            ->download($nombre);
    }

    public function excel(Request $request): StreamedResponse
    {
        $filtros = $this->validarFiltros($request);
        $datos = $this->obtenerDatosReporte($filtros);
        $etiquetas = $this->etiquetasFiltros($filtros);
        $spreadsheet = new Spreadsheet();

        $this->crearHojaResumen($spreadsheet, $datos, $etiquetas);
        $this->crearHojaDetalle($spreadsheet, $datos, $etiquetas);

        $spreadsheet->setActiveSheetIndex(0);
        $nombre = "reporte-productos-{$filtros['fecha_desde']}-{$filtros['fecha_hasta']}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function clientes(int $almacenId): JsonResponse
    {
        Almacen::query()->findOrFail($almacenId);

        $clientes = Cliente::query()
            ->where('cliente_almacen_id', $almacenId)
            ->where('cliente_estado', 1)
            ->orderBy('cliente_nombre')
            ->orderBy('cliente_apellido')
            ->get(['cliente_id', 'cliente_nombre', 'cliente_apellido', 'cliente_cedula'])
            ->map(fn (Cliente $cliente) => [
                'id' => $cliente->cliente_id,
                'text' => trim("{$cliente->cliente_nombre} {$cliente->cliente_apellido}")
                    . " ({$cliente->cliente_cedula})",
            ]);

        return response()->json(['clientes' => $clientes]);
    }

    private function validarFiltros(Request $request): array
    {
        $request->merge([
            'fecha_desde' => $request->input('fecha_desde', now()->startOfMonth()->toDateString()),
            'fecha_hasta' => $request->input('fecha_hasta', now()->toDateString()),
        ]);

        return $request->validate([
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['required', 'date', 'after_or_equal:fecha_desde'],
            'almacen_id' => ['nullable', 'integer', 'exists:almacen,almacen_id'],
            'cliente_id' => ['nullable', 'integer', 'exists:cliente,cliente_id'],
            'receta_tipo' => ['nullable', 'in:0,1'],
        ]);
    }

    private function obtenerDatosReporte(array $filtros): array
    {
        $consulta = $this->consultaBase($filtros);

        $resumen = (clone $consulta)
            ->select([
                'p.producto_id',
                'p.producto_nombre',
                'p.producto_concentracion',
                'p.producto_presentacion',
                'um.unidad_medida_detalle',
                DB::raw('SUM(rp.producto_cantidad) AS cantidad_total'),
                DB::raw('COUNT(DISTINCT r.receta_id) AS total_recetas'),
            ])
            ->groupBy(
                'p.producto_id',
                'p.producto_nombre',
                'p.producto_concentracion',
                'p.producto_presentacion',
                'um.unidad_medida_detalle'
            )
            ->orderBy('p.producto_nombre')
            ->orderBy('p.producto_concentracion')
            ->get();

        $detalle = (clone $consulta)
            ->select([
                'r.fecha_emision',
                'p.producto_id',
                'p.producto_nombre',
                'p.producto_concentracion',
                'p.producto_presentacion',
                'um.unidad_medida_detalle',
                DB::raw('SUM(rp.producto_cantidad) AS cantidad_total'),
                DB::raw('COUNT(DISTINCT r.receta_id) AS total_recetas'),
            ])
            ->groupBy(
                'r.fecha_emision',
                'p.producto_id',
                'p.producto_nombre',
                'p.producto_concentracion',
                'p.producto_presentacion',
                'um.unidad_medida_detalle'
            )
            ->orderBy('r.fecha_emision')
            ->orderBy('p.producto_nombre')
            ->get()
            ->groupBy('fecha_emision');

        return [
            'resumen' => $resumen,
            'detalle' => $detalle,
            'totales' => [
                'productos' => $resumen->count(),
                'recetas' => (clone $consulta)->distinct()->count('r.receta_id'),
                'cantidad' => $resumen->sum('cantidad_total'),
            ],
        ];
    }

    private function etiquetasFiltros(array $filtros): array
    {
        $registroAlmacen = !empty($filtros['almacen_id'])
            ? Almacen::query()->find($filtros['almacen_id'])
            : null;

        $logoAlmacen = null;
        if ($registroAlmacen) {
            $rutaLogo = $registroAlmacen->almacen_logo
                ? storage_path('app/public/' . $registroAlmacen->almacen_logo)
                : null;
            $logoAlmacen = $rutaLogo && is_file($rutaLogo)
                ? $rutaLogo
                : public_path('img/sin_logo.png');
        }

        $cliente = null;
        if (!empty($filtros['cliente_id'])) {
            $registro = Cliente::query()->find($filtros['cliente_id']);
            $cliente = $registro
                ? trim("{$registro->cliente_nombre} {$registro->cliente_apellido}")
                    . " ({$registro->cliente_cedula})"
                : null;
        }

        $tipo = match ((string) ($filtros['receta_tipo'] ?? '')) {
            '0' => 'Agrícola',
            '1' => 'Veterinaria',
            default => 'Todos',
        };

        return [
            'periodo' => "{$filtros['fecha_desde']} al {$filtros['fecha_hasta']}",
            'almacen' => $registroAlmacen?->almacen_nombre ?: 'Todos',
            'logo_almacen' => $logoAlmacen,
            'cliente' => $cliente ?: 'Todos',
            'tipo' => $tipo,
        ];
    }

    private function crearHojaResumen(Spreadsheet $spreadsheet, array $datos, array $etiquetas): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen');
        $sheet->mergeCells('A1:E1')->setCellValue('A1', 'REPORTE DE PRODUCTOS RECETADOS');
        $sheet->setCellValue('A2', 'Período')->setCellValue('B2', $etiquetas['periodo']);
        $sheet->setCellValue('A3', 'Almacén')->setCellValue('B3', $etiquetas['almacen']);
        $sheet->setCellValue('C3', 'Cliente')->setCellValue('D3', $etiquetas['cliente']);
        $sheet->setCellValue('A4', 'Tipo')->setCellValue('B4', $etiquetas['tipo']);
        $sheet->setCellValue('C4', 'Recetas')->setCellValue('D4', $datos['totales']['recetas']);
        $sheet->fromArray(['Producto', 'Concentración', 'Presentación', 'Recetas', 'Cantidad total'], null, 'A6');

        $fila = 7;
        foreach ($datos['resumen'] as $producto) {
            $sheet->fromArray([
                $producto->producto_nombre,
                $producto->producto_concentracion,
                trim("{$producto->producto_presentacion} {$producto->unidad_medida_detalle}"),
                (int) $producto->total_recetas,
                (float) $producto->cantidad_total,
            ], null, "A{$fila}");
            $fila++;
        }

        $this->aplicarEstiloHoja($sheet, 6, max(6, $fila - 1));
        $sheet->getStyle('D7:D' . max(7, $fila - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E7:E' . max(7, $fila - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('13854A');
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(38);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->freezePane('A7');
        $sheet->setAutoFilter("A6:E" . max(6, $fila - 1));
    }

    private function crearHojaDetalle(Spreadsheet $spreadsheet, array $datos, array $etiquetas): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Detalle diario');
        $sheet->mergeCells('A1:F1')->setCellValue('A1', 'DETALLE DIARIO DE PRODUCTOS RECETADOS');
        $sheet->setCellValue('A2', 'Período')->setCellValue('B2', $etiquetas['periodo']);
        $sheet->setCellValue('C2', 'Almacén')->setCellValue('D2', $etiquetas['almacen']);
        $sheet->setCellValue('E2', 'Tipo')->setCellValue('F2', $etiquetas['tipo']);
        $sheet->setCellValue('A3', 'Cliente')->setCellValue('B3', $etiquetas['cliente']);
        $sheet->fromArray(
            ['Fecha', 'Producto', 'Concentración', 'Presentación', 'Recetas', 'Cantidad'],
            null,
            'A5'
        );

        $fila = 6;
        foreach ($datos['detalle'] as $fecha => $productos) {
            foreach ($productos as $producto) {
                $sheet->fromArray([
                    \Carbon\Carbon::parse($fecha)->format('d/m/Y'),
                    $producto->producto_nombre,
                    $producto->producto_concentracion,
                    trim("{$producto->producto_presentacion} {$producto->unidad_medida_detalle}"),
                    (int) $producto->total_recetas,
                    (float) $producto->cantidad_total,
                ], null, "A{$fila}");
                $fila++;
            }
        }

        $this->aplicarEstiloHoja($sheet, 5, max(5, $fila - 1), 'F');
        $sheet->getStyle('E6:E' . max(6, $fila - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F6:F' . max(6, $fila - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('13854A');
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->freezePane('A6');
        $sheet->setAutoFilter("A5:F" . max(5, $fila - 1));
    }

    private function aplicarEstiloHoja($sheet, int $filaEncabezado, int $ultimaFila, string $ultimaColumna = 'E'): void
    {
        $sheet->getStyle("A{$filaEncabezado}:{$ultimaColumna}{$filaEncabezado}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '13854A'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A{$filaEncabezado}:{$ultimaColumna}{$ultimaFila}")
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function consultaBase(array $filtros): Builder
    {
        return DB::table('receta_producto as rp')
            ->join('receta as r', 'r.receta_id', '=', 'rp.receta_id')
            ->join('receta_lote as rl', 'rl.receta_lote_id', '=', 'r.receta_lote_id')
            ->join('producto as p', 'p.producto_id', '=', 'rp.producto_id')
            ->leftJoin('unidad_medida as um', 'um.unidad_medida_id', '=', 'p.unidad_medida_id')
            ->where('rl.receta_lote_estado', 1)
            ->whereBetween('r.fecha_emision', [$filtros['fecha_desde'], $filtros['fecha_hasta']])
            ->when(!empty($filtros['almacen_id']), fn (Builder $query) =>
                $query->where('rl.almacen_id', $filtros['almacen_id']))
            ->when(!empty($filtros['cliente_id']), fn (Builder $query) =>
                $query->where('r.cliente_id', $filtros['cliente_id']))
            ->when(array_key_exists('receta_tipo', $filtros) && $filtros['receta_tipo'] !== null
                && $filtros['receta_tipo'] !== '', fn (Builder $query) =>
                $query->where('rl.receta_tipo', $filtros['receta_tipo']));
    }
}
