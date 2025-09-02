<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Receta Veterinaria</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        .mini {
            padding: 8px;
        }

        .header,
        .section,
        .footer {
            border: 1px solid #000;
            padding: 4px;
            margin-bottom: 10px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            background: #d6eaf8;
            margin-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
            font-size: 9px;
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
                            <img src="{{ public_path('img/AGROCALIDAD.png') }}" class="logo" alt="Logo 1"><br>
                            <img src="{{ public_path('img/logo-veterinaria.jpg') }}" class="logo" alt="Logo 2">
                        </td>
                        <td style="width: 40%; text-align: center; font-size: 12px;">
                            <strong>REPRESENTACIONES TÉCNICAS PARA ALMACENISTA AGROPECUARIOS</strong><br>
                            MEDICO VETERINARIO - SENESCYT {{ $recetaLote->tecnico->tecnico_senescyt ?? '' }}<br>
                            {{ $recetaLote->tecnico->tecnico_apellido }} {{ $recetaLote->tecnico->tecnico_nombre }} <br>
                            C.I.: {{ $recetaLote->tecnico->tecnido_cedula }}<br>
                            TELÉFONO: {{ $recetaLote->tecnico->tecnico_telefono }}<br>
                            <strong>RECETA VETERINARIA PARA EXPENDIO DE PLAGUICIDAS</strong>
                        </td>
                        <td style="width: 30%; text-align: center;">
                            <div>
                                <img src="{{ $recetaLote->almacen->almacen_logo ? public_path('storage/' . $recetaLote->almacen->almacen_logo) : public_path('img/sin_logo.png') }}"
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
                <div class="title">RECETA VETERINARIA PARA EXPENDIO DE PLAGUICIDAS</div>
                <table>
                    <tr>
                        <td style="width: 25%;">ALMACÉN:</td>
                        <td style="width: 25%;">{{ $recetaLote->almacen->almacen_nombre ?? '' }}</td>
                        <td style="width: 25%;">FECHA:</td>
                        <td style="width: 25%;">{{ \Carbon\Carbon::parse($receta->fecha_emision)->format('d/m/Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td>DIRECCIÓN:</td>
                        <td>{{ $recetaLote->almacen->almacen_direccion ?? '' }}</td>
                        <td>PROPIETARIO:</td>
                        <td>{{ $recetaLote->almacen->propietario->propietario_almacen_nombre ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="title">INFORMACIÓN DEL PACIENTE Y PRESCRIPCIÓN</div>
                <table>
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Especie</th>
                            <th>Subespecie</th>
                            <th>Sexo</th>
                            <th>Edad</th>
                            <th>Forma farmacéutica</th>
                            <th>Principio activo</th>
                            <th>Unidades por envase</th>
                            <th>Vía de administración</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i = 1;
                            $maxFilas = 5;
                        @endphp
                        @foreach ($receta->productos as $detalle)
                            @php
                                $producto = $detalle->producto;
                                $dosificacion = $detalle->dosificacion;
                                $subespecie = $dosificacion->subespecie ?? null;
                                $especie = $subespecie->especie ?? null;
                                $ingredientes = $producto->ingredientes;
                                $sexo = '-';
                                if (is_array($subespecie->sexos) && count($subespecie->sexos) > 0) {
                                    $sexo = strtoupper($subespecie->sexos[array_rand($subespecie->sexos)]);
                                }

                                // Generar edad aleatoria entre min y max
                                $edad = '-';
                                if (
                                    is_numeric($subespecie->edad_min) &&
                                    is_numeric($subespecie->edad_max) &&
                                    $subespecie->edad_min <= $subespecie->edad_max
                                ) {
                                    $randomEdad = rand($subespecie->edad_min, $subespecie->edad_max);
                                    $edad = $randomEdad . ' ' . strtoupper($subespecie->unidad_edad);
                                }
                                $principios = $ingredientes
                                    ->map(function ($ing) {
                                        return ($ing->ingredienteActivo->ingrediente_activo_nombre ?? 'N/A') .
                                            ' (' .
                                            ($ing->cantidad ?? '-') .
                                            ' ' .
                                            ($ing->unidadMedida->unidad_medida_detalle ?? '') .
                                            ')';
                                    })
                                    ->implode(', ');
                            @endphp
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $especie->especie_nombre ?? '-' }}</td>
                                <td>{{ $subespecie->subespecie_nombre ?? '-' }}</td>
                                <td>{{ $sexo }}</td>
                                <td>{{ $edad }}</td>
                                <td>{{ $producto->producto_nombre }} {{ $producto->producto_concentracion }}
                                    {{ $producto->producto_presentacion }}
                                    {{ $producto->unidadMedida->unidad_medida_detalle ?? '' }}</td>
                                <td>{{ $principios }}</td>
                                <td>{{ $producto->producto_unidad_en_envase ?? '-' }}</td>
                                <td>{{ $dosificacion->dosificacion_aplicacion ?? '-' }}</td>
                            </tr>
                            @php $i++; @endphp
                        @endforeach
                        @for (; $i <= $maxFilas; $i++)
                            <tr>
                                <td>{{ $i }}</td>
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

            <div class="section">
                <div class="title">DIAGNÓSTICO</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">Nº</th>
                            <th>Diagnóstico</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach ($receta->productos as $detalle)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $detalle->producto->producto_diagnostico ?? '-' }}</td>
                            </tr>
                            @php $i++; @endphp
                        @endforeach
                        @for (; $i <= 6; $i++)
                            <tr>
                                <td>{{ $i }}</td>
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
                                style="width: 70px; height: 70px;"><br>
                        @else
                            <img src="{{ public_path('firma-qr.png') }}" style="width: 70px; height:70px;"><br>
                        @endif
                        <div style="font-size: 10px;">FIRMA</div>
                    </div>
                    @if (isset($qrImage))
                        <div style="font-size: 9px; text-align: center;">
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
