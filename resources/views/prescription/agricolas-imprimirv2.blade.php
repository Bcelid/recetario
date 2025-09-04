<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recetas Agrícolas</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }


        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .mini {
            padding: 4px;
        }


        .header,
        .section,
        .footer {
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 5px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            background: #d6eaf8;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 10px;
            word-wrap: break-word;
        }

        .no-border td {
            border: none;
            padding: 4px;
        }

        .firma-sello {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .firma-sello div {
            width: 100%;
            text-align: center;
        }

        img.logo {
            height: 50px;
            margin-top: 5px;
            object-fit: contain;
        }
    </style>
</head>

<body>

    @foreach ($recetaLote->recetas as $receta)
        @php
            $cliente = $receta->cliente;
        @endphp

        <div class="mini">
            <div class="header">
                <table class="no-border">
    <tr>
        <td style="width: 30%; text-align: center;">
            <img src="{{ url('img/AGROCALIDAD.png') }}" class="logo" alt="Logo 1"><br>
            <img src="{{ url('img/logo-agropecuario.jpg') }}" class="logo" alt="Logo 2">
        </td>
        <td style="width: 40%; text-align: center; font-size: 12px;">
            <strong>REPRESENTACIONES TÉCNICAS PARA ALMACENISTA AGROPECUARIOS</strong><br>
            ING. AGRÓNOMO - SENESCYT {{ $recetaLote->tecnico->tecnico_senescyt ?? '' }}<br>
            {{ $recetaLote->tecnico->tecnico_apellido }} {{ $recetaLote->tecnico->tecnico_nombre }} <br>
            C.I.: {{ $recetaLote->tecnico->tecnido_cedula }}<br>
            TELÉFONO: {{ $recetaLote->tecnico->tecnico_telefono }}<br>
            <strong>RECETA AGRICOLA PARA EXPENDIO DE PLAGUICIDAS</strong>
        </td>
        <td style="width: 30%; text-align: center;">
            <div>
                <img src="{{ $recetaLote->almacen->almacen_logo
                    ? url('storage/' . $recetaLote->almacen->almacen_logo)
                    : url('img/sin_logo.png') }}"
                    class="logo mt-2px" alt="Logo Almacén"
                    style="max-height: 80px; display: block; margin: 0 auto;">

                <div>
                    <h2><strong style="color: red;">NO. {{ $receta->receta_numero }}</strong></h2>
                </div>
            </div>
        </td>
    </tr>
</table>

            </div>

            <div class="section">
                <div class="title">RECETA AGRÍCOLA PARA EXPENDIO DE PLAGUICIDAS</div>
                <table>
                    <tr>
                        <td style="width: 25%;">PROPIETARIO:</td>
                        <td style="width: 25%;">
                            {{ $recetaLote->almacen->propietario->propietario_almacen_nombre ?? '' }}</td>
                        <td style="width: 20%;">ALMACÉN:</td>
                        <td style="width: 25%;">{{ $recetaLote->almacen->almacen_nombre ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>DIRECCIÓN:</td>
                        <td>{{ $recetaLote->almacen->almacen_direccion ?? '' }}</td>
                        <td>FECHA:</td>
                        <td>{{ \Carbon\Carbon::parse($receta->fecha_emision)->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="title">INFORMACIÓN DE CULTIVO</div>
                <table>
                    <tr>
                        <td style="width: 25%;">PROPIETARIO:</td>
                        <td style="width: 25%;">{{ $cliente->cliente_nombre }} {{ $cliente->cliente_apellido }}</td>
                        <td style="width: 20%;">DIRECCIÓN:</td>
                        <td style="width: 25%;">{{ $cliente->cliente_direccion }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="title">PRESCRIPCIÓN</div>
                <table>
                    <thead>
                        <tr>
                            <th>Cultivo</th>
                            <th>Plaga</th>
                            <th>Forma Farmacéutica</th>
                            <th>Formulación</th>
                            <th>Cantidad</th>
                            <th>Volumen a tratarse</th>
                            <th>Principios Activos</th>
                            <th>Recomendación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $productos = $receta->productos;
                            $totalProductos = $productos->count();
                        @endphp

                        @foreach ($productos as $detalle)
                            @php
                                $producto = $detalle->producto;
                                $dosificacion = $detalle->dosificacion;
                                $formulacion = $producto->formulacion->formulacion_nombre ?? '';
                                $formulacion_abrev = $producto->formulacion->formulacion_abreviatura ?? '';
                                $ingredientes = $producto->ingredientes ?? collect();

                                $principios = $ingredientes
                                    ->map(function ($ing) {
                                        return ($ing->ingredienteActivo->ingrediente_activo_nombre ?? 'N/A') .
                                            ' ' .
                                            ($ing->cantidad ?? '-') .
                                            ' ' .
                                            ($ing->unidadMedida->unidad_medida_detalle ?? '');
                                    })
                                    ->implode(', ');
                            @endphp
                            <tr>
                                <td>{{ $dosificacion->cultivo->cultivo_nombre ?? '-' }}</td>
                                <td>{{ $dosificacion->maleza->maleza_nombre ?? '-' }}</td>
                                <td>{{ $producto->producto_nombre ?? '-' }}</td>
                                <td>{{ $formulacion . ' (' . $formulacion_abrev . ')' }}</td>
                                <td>{{ $detalle->producto_cantidad }}</td>
                                <td>{{ $detalle->producto_cantidad * $dosificacion->dosis }} HA</td>
                                <td>{{ $principios }}</td>
                                <td>{{ $dosificacion->dosificacion_aplicacion ?? '-' }}</td>
                            </tr>
                        @endforeach

                        {{-- Agregar filas vacías hasta llegar a 6 --}}
                        @for ($i = $totalProductos; $i < 5; $i++)
                            <tr>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        @endfor
                    </tbody>

                </table>
            </div>

            <div class="footer">
                <table>
                    <tr>
                        <td colspan="2"><strong>PROFESIONAL:</strong> {{ $recetaLote->tecnico->tecnico_nombre }}
                            {{ $recetaLote->tecnico->tecnico_apellido }}</td>
                        <td><strong>SENESCYT:</strong> {{ $recetaLote->tecnico->tecnico_senescyt }}</td>
                    </tr>
                </table>
                <div class="firma-sello">
                    <div>
                        @if (isset($qrImage))
                            <img src="data:image/png;base64,{{ $qrImage }}"
                                style="width: 80px; height: 80px;"><br>
                        @else
                            <img src="{{ public_path('firma-qr.png') }}" style="width: 80px; height: 80px;"><br>
                        @endif
                        <div style="font-size: 12px;">FIRMA</div>
                    </div>

                    @if (isset($qrImage))
                        <div style="font-size: 10px;">
                            <strong>{{ $recetaLote->tecnico->tecnico_nombre }}
                                {{ $recetaLote->tecnico->tecnico_apellido }}</strong><br>
                            {{ $recetaLote->tecnico->categoria->tecnico_categoria_nombre ?? 'Sin categoría' }}<br>
                            Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                        </div>
                    @endif
                </div>

            </div>

        </div>
    @endforeach

</body>

</html>
