<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de productos recetados</title>
    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            color: #212529;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        h1 {
            color: #13854A;
            font-size: 18px;
            margin: 0 0 4px;
            text-align: center;
        }

        h2 {
            color: #13854A;
            font-size: 13px;
            margin: 16px 0 6px;
            page-break-after: avoid;
        }

        .subtitle {
            color: #6c757d;
            text-align: center;
        }

        .report-header {
            border-collapse: collapse;
            margin-bottom: 12px;
            width: 100%;
        }

        .report-header td {
            vertical-align: middle;
        }

        .logo-cell,
        .warehouse-logo-cell {
            width: 22%;
        }

        .logo-cell img,
        .warehouse-logo-cell img {
            height: auto;
            max-height: 58px;
            max-width: 130px;
        }

        .warehouse-logo-cell {
            text-align: right;
        }

        .title-cell {
            text-align: center;
            width: 56%;
        }

        .filters,
        .summary,
        .data-table {
            border-collapse: collapse;
            width: 100%;
        }

        .filters td {
            background: #f3f6fa;
            border: 1px solid #d9e2ef;
            padding: 6px;
            width: 25%;
        }

        .label {
            color: #5b6778;
            display: block;
            font-size: 8px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .summary {
            margin-top: 10px;
        }

        .summary td {
            border: 1px solid #d9e2ef;
            padding: 7px;
            text-align: center;
            width: 33.33%;
        }

        .summary strong {
            color: #13854A;
            display: block;
            font-size: 14px;
        }

        .data-table {
            page-break-inside: auto;
        }

        .data-table th {
            background: #13854A;
            color: #ffffff;
            padding: 5px;
            text-align: left;
        }

        .data-table td {
            border-bottom: 1px solid #dfe5ec;
            padding: 4px 5px;
        }

        .data-table tbody tr:nth-child(even) td {
            background: #f7f9fb;
        }

        .number {
            text-align: right !important;
        }

        .day-title {
            background: #e8f5ee;
            border-left: 4px solid #13854A;
            color: #13854A;
            font-size: 11px;
            font-weight: bold;
            margin: 12px 0 0;
            padding: 6px 8px;
        }

        .day-block {
            page-break-inside: avoid;
        }

        .empty {
            color: #6c757d;
            padding: 14px;
            text-align: center;
        }

        .footer-note {
            color: #6c757d;
            font-size: 8px;
            margin-top: 12px;
            text-align: right;
        }
    </style>
</head>

<body>
    @php
        $formatQuantity = static function ($value) {
            $decimals = floor((float) $value) == (float) $value ? 0 : 2;
            return number_format((float) $value, $decimals, ',', '.');
        };
    @endphp

     <table class="report-header">
        <tr>
            <td class="logo-cell">
                <img src="{{ url('img/Logocombinado_sinfondo.png') }}"
                        alt="Representaciones Coagvelcor"
                    >
            </td>
            <td class="title-cell">
                <h1>Reporte de productos recetados</h1>
                <div class="subtitle">Totales por producto y detalle diario según la fecha de emisión</div>
            </td>
            <td class="warehouse-logo-cell">
                @if ($etiquetas['logo_almacen'])
                    <img src="{{ $etiquetas['logo_almacen'] }}" alt="Logo del almacén">
                @endif
            </td>
        </tr>
    </table>

    <table class="filters">
        <tr>
            <td><span class="label">Período</span>{{ $etiquetas['periodo'] }}</td>
            <td><span class="label">Almacén</span>{{ $etiquetas['almacen'] }}</td>
            <td><span class="label">Cliente</span>{{ $etiquetas['cliente'] }}</td>
            <td><span class="label">Tipo de receta</span>{{ $etiquetas['tipo'] }}</td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td><span class="label">Productos diferentes</span><strong>{{ $totales['productos'] }}</strong></td>
            <td><span class="label">Recetas incluidas</span><strong>{{ $totales['recetas'] }}</strong></td>
            <td><span class="label">Cantidad total</span><strong>{{ $formatQuantity($totales['cantidad']) }}</strong></td>
        </tr>
    </table>

    <h2>Resumen por producto</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Concentración</th>
                <th>Presentación</th>
                <th class="number">Recetas</th>
                <th class="number">Cantidad total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($resumen as $producto)
                <tr>
                    <td>{{ $producto->producto_nombre }}</td>
                    <td>{{ $producto->producto_concentracion }}</td>
                    <td>
                        {{ $formatQuantity($producto->producto_presentacion) }}
                        {{ $producto->unidad_medida_detalle }}
                    </td>
                    <td class="number">{{ $producto->total_recetas }}</td>
                    <td class="number">{{ $formatQuantity($producto->cantidad_total) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No se encontraron resultados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detalle por fecha</h2>
    @forelse ($detalle as $fecha => $productos)
        <div class="day-block">
            <div class="day-title">
                {{ \Carbon\Carbon::parse($fecha)->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Concentración</th>
                        <th>Presentación</th>
                        <th class="number">Recetas</th>
                        <th class="number">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        <tr>
                            <td>{{ $producto->producto_nombre }}</td>
                            <td>{{ $producto->producto_concentracion }}</td>
                            <td>
                                {{ $formatQuantity($producto->producto_presentacion) }}
                                {{ $producto->unidad_medida_detalle }}
                            </td>
                            <td class="number">{{ $producto->total_recetas }}</td>
                            <td class="number">{{ $formatQuantity($producto->cantidad_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="empty">No hay detalle diario para los filtros seleccionados.</div>
    @endforelse

    <div class="footer-note">Generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>

</html>
