<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Receta Agrícola</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 9pt;
        }

        /* Página A4 en mm (landscape) */
        .page {
            width: 270mm;
            height: 180mm;
            box-sizing: border-box;
            page-break-after: always;
        }

        .page-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .page-cell {
            width: 50%;
            vertical-align: top;
            padding: 2mm;
            box-sizing: border-box;
            border-right: 1px solid #000;
        }

        .page-cell:last-child {
            border-right: none;
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .logo-block {
            text-align: center;
            padding: 5px;
        }

        .logo-block img {
            max-height: 35px;
            display: block;
            margin: 0 auto 5px auto;
        }

        .table-fecha {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .table-fecha td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
        }

        .receta-numero {
            margin-top: 5px;
            padding: 5px;
            font-weight: bold;
            color: red;
            text-align: center;
            font-size: 14px;
        }

        .encabezado {
            width: 100%;
            height: auto;
            border: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .encabezado-sinborder {
            width: 100%;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    @php
    $cliente = $receta->cliente;
    $fecha = \Carbon\Carbon::parse($receta->fecha_emision);
    @endphp

    <div class="page">
        <table class="page-table">
            <tr>
                <!-- Parte Izquierda -->
                <td class="page-cell">
                    <div class="encabezado-sinborder">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 60%; text-align: center; vertical-align: top;">
                                    <table class="table-fecha" style="margin: 0 auto;">
                                        <tr>
                                            <td colspan="3" style="font-weight: bold;">Fecha de emisión</td>
                                        </tr>
                                        <tr>
                                            <td>Día</td>
                                            <td>Mes</td>
                                            <td>Año</td>
                                        </tr>
                                        <tr>
                                            <td>{{ $fecha->format('d') }}</td>
                                            <td>{{ $fecha->format('m') }}</td>
                                            <td>{{ $fecha->format('Y') }}</td>
                                        </tr>
                                    </table>

                                    <h4 style="margin-top: 8px; margin-bottom: 0; font-weight: bold; text-align: left;">
                                        RECETA AGRÍCOLA
                                    </h4>
                                    
                                </td>


                                <td style="width: 40%; text-align: center; vertical-align: top;">
                                    <img src="{{ $recetaLote->almacen->almacen_logo
                                        ? url('storage/' . $recetaLote->almacen->almacen_logo)
                                        : url('img/sin_logo.png') }}"
                                        alt="Logo Almacén"
                                        style="max-height: 50px; display: block; margin: 0 auto;">
                                    <div class="receta-numero">N. {{ $receta->receta_numero }}</div>
                                    @if($receta->receta_factura && $receta->receta_factura != '000-000-000000000')
                                    <div style="font-size:11px; font-weight:bold; margin-top:2px;">
                                        N° Factura: {{ $receta->receta_factura }}
                                    </div>
                                    @endif
                                </td>

                            </tr>
                        </table>
                    </div>

                    <div class="encabezado-sinborder">
                        <table style="width: 100%; border-collapse: collapse; font-size:9pt;">
                            <tr style="background-color: #e0e0e0; font-weight: bold;">
                                <td style="border: 1px solid #000; padding: 4px;" colspan="2">
                                    Información del profesional que prescribe:
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px; width: 30%;">Nombres y apellidos:
                                </td>
                                <td style="border: 1px solid #000; padding: 4px; width: 70%;">
                                    {{ $recetaLote->tecnico->tecnico_apellido }}
                                    {{ $recetaLote->tecnico->tecnico_nombre }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px;">Cédula de identidad:</td>
                                <td style="border: 1px solid #000; padding: 4px;">
                                    {{ $recetaLote->tecnico->tecnido_cedula }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px;">N° Registro SENESCYT:</td>
                                <td style="border: 1px solid #000; padding: 4px;">
                                    {{ $recetaLote->tecnico->tecnico_senescyt ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px;">Teléfono:</td>
                                <td style="border: 1px solid #000; padding: 4px;">
                                    {{ $recetaLote->tecnico->tecnico_telefono }}
                                </td>
                            </tr>
                            <tr style="background-color: #e0e0e0; font-weight: bold;">
                                <td style="border: 1px solid #000; padding: 4px;" colspan="2">
                                    Información del Almacen:
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px;">Propietario:</td>
                                <td style="border: 1px solid #000; padding: 4px;">
                                    {{ $recetaLote->almacen->propietario->propietario_almacen_nombre ?? '' }} {{ $recetaLote->almacen->propietario->propietario_almacen_apellido ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px;">Direccion:</td>
                                <td style="border: 1px solid #000; padding: 4px;">
                                    {{ $recetaLote->almacen->almacen_direccion ?? '' }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="encabezado-final">
                        <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
                            <tr style="background-color: #e0e0e0; font-weight: bold;">
                                <td style="border: 1px solid #000; padding: 2px;" colspan="4">
                                    Prescripción
                                    (Forma farmaceutica, principio activo, concentración, unidad, formulación;
                                    incluir
                                    cultivo,
                                    plaga, dosis y volumen/área a tratarse)
                                </td>
                            </tr>

                            @php
                            $productos = $receta->productos;
                            @endphp
                            <td style="
    border: 1px solid #000;
    height: 120px;
    vertical-align: top;
    word-break: break-word;
    overflow-wrap: break-word;
    overflow: hidden;
    padding: 2px 2px 2px 10px;"
                                colspan="4">
                                @for ($i = 0; $i < 3; $i++)
                                    @if (isset($productos[$i]))
                                    @php
                                    $detalle=$productos[$i];
                                    $producto=$detalle->producto;
                                    $dosificacion = $detalle->dosificacion;
                                    $formulacion = $producto->formulacion->formulacion_nombre ?? '';
                                    $formulacion_abrev =
                                    $producto->formulacion->formulacion_abreviatura ?? '';
                                    $ingredientes = $producto->ingredientes ?? collect();
                                    $cultivo = $dosificacion->cultivo->cultivo_nombre ?? '-';
                                    $maleza = $dosificacion->maleza->maleza_nombre ?? '-';
                                    $principios = $ingredientes
                                    ->map(function ($ing) {
                                    return ($ing->ingredienteActivo->ingrediente_activo_nombre ??
                                    'N/A') .
                                    ' ' .
                                    ($ing->cantidad ?? '-') .
                                    ' ' .
                                    ($ing->unidadMedida->unidad_medida_detalle ?? '');
                                    })
                                    ->implode(', ');

                                    $texto = $dosificacion->dosificacion_aplicacion ?? '-';
                                    $partes = explode('//', $texto);

                                    if (count($partes) > 1) {
                                    $cuerpo1 = trim($partes[0]);
                                    $cuerpo2 = trim($partes[1]);
                                    } else {
                                    $cuerpo1 = $cuerpo2 = trim($texto);
                                    }
                                    @endphp
                                    <br>
                                    <strong>{{ $i + 1 }})</strong>
                                    {{ round($detalle->producto_cantidad) }} {{ $producto->producto_nombre }}
                                    {{ $producto->producto_concentracion }}
                                    {{ fmod($producto->producto_presentacion, 1) == 0
                                                ? number_format($producto->producto_presentacion, 0)
                                                : number_format($producto->producto_presentacion, 1) }}
                                    {{ $producto->unidadMedida->unidad_medida_detalle ?? '' }}
                                    {{ $formulacion . ' (' . $formulacion_abrev . ')' }}
                                    {{ $detalle->producto_cantidad * $dosificacion->dosis }}HA
                                    {{ $cultivo }} - {{ $maleza }}
                                    {{ $cuerpo1 ?? '-' }} - Principio(s)
                                    Activo(s): {{ $principios }}<br>
                                    @else
                                    <strong>{{ $i + 1 }})</strong> -<br>
                                    @endif
                                    @endfor
                            </td>


                            <tr>
                                <td style="border: 1px solid #000; padding: 4px; height: 80px; text-align: center; vertical-align: middle;"
                                    colspan="4">
                                    <div style="display: inline-block; text-align: center;">
                                        @if (isset($qrImage))
                                        <img src="data:image/png;base64,{{ $qrImage }}"
                                            style="width: 60px; height: 60px;"><br>
                                        @else
                                        <img src="{{ public_path('firma-qr.png') }}"
                                            style="width: 60px; height: 60px;"><br>
                                        @endif
                                        @if (isset($qrImage))
                                        <div style="font-size: 10px;">
                                            <strong>{{ $recetaLote->tecnico->tecnico_nombre }}
                                                {{ $recetaLote->tecnico->tecnico_apellido }}</strong><br>
                                            {{ $recetaLote->tecnico->categoria->tecnico_categoria_nombre ?? 'Sin categoría' }}<br>
                                            Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <p class="text-center"><strong>Cuerpo 1 original: Almacenista</strong></p>
                    </div>
                </td>

                <!-- Parte Derecha -->
                <td class="page-cell">
                    <div class="encabezado-sinborder">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 60%; text-align: center; vertical-align: top;">
                                    <table class="table-fecha" style="margin: 0 auto;">
                                        <tr>
                                            <td colspan="3" style="font-weight: bold;">Fecha de emisión</td>
                                        </tr>
                                        <tr>
                                            <td>Día</td>
                                            <td>Mes</td>
                                            <td>Año</td>
                                        </tr>
                                        <tr>
                                            <td>{{ $fecha->format('d') }}</td>
                                            <td>{{ $fecha->format('m') }}</td>
                                            <td>{{ $fecha->format('Y') }}</td>
                                        </tr>
                                    </table>

                                    <h4 style="margin-top: 8px; margin-bottom: 0; font-weight: bold; text-align: left;">
                                        RECETA AGRÍCOLA
                                    </h4>
                                   
                                </td>

                                <td style="width: 40%; text-align: center; vertical-align: top;">
                                    <img src="{{ $recetaLote->almacen->almacen_logo
                                        ? url('storage/' . $recetaLote->almacen->almacen_logo)
                                        : url('img/sin_logo.png') }}"
                                        alt="Logo Almacén"
                                        style="max-height: 50px; display: block; margin: 0 auto;">
                                    <div class="receta-numero">N. {{ $receta->receta_numero }}</div>
                                     @if($receta->receta_factura && $receta->receta_factura != '000-000-000000000')
                                    <div style="font-size:11px; font-weight:bold; margin-top:2px;">
                                        N° Factura: {{ $receta->receta_factura }}
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="encabezado-sinborder">
                        <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
                            <tr style="background-color: #e0e0e0; font-weight: bold;">
                                <td style="border: 1px solid #000; padding: 2px;" colspan="2">
                                    Información del cultivo:
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px; width: 30%;">Cultivo a tratar y
                                    área de cultivo</td>
                                @php
                                $productos = $receta->productos;
                                @endphp
                                <td style="border: 1px solid #000; padding: 4px; width: 70%;">
                                    @for ($i = 0; $i < 3; $i++)
                                        @if (isset($productos[$i]))
                                        @php
                                        $dosificacion=$productos[$i]->dosificacion;
                                        $cultivo = $dosificacion->cultivo->cultivo_nombre ?? '-';
                                        $maleza = $dosificacion->maleza->maleza_nombre ?? '-';
                                        @endphp
                                        <strong>{{ $i + 1 }})</strong> {{ $cultivo }} - {{ $maleza }}<br>
                                        @else
                                        <strong>{{ $i + 1 }})</strong> -<br>
                                        @endif
                                        @endfor
                                </td>

                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px;">Nombre del propietario:</td>
                                <td style="border: 1px solid #000; padding: 4px;">
                                    {{ $cliente->cliente_nombre }} {{ $cliente->cliente_apellido }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px;">Dirección del propietario:</td>
                                <td style="border: 1px solid #000; padding: 4px;">
                                    {{ $cliente->cliente_direccion }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="encabezado-sinborder">
                        <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
                            <tr style="background-color: #e0e0e0; font-weight: bold;">
                                <td style="border: 1px solid #000; padding: 4px;" colspan="4">
                                    Indicaciones
                                    <br>(Dosis, aplicación, frecuencia):
                                </td>
                            </tr>

                            @php
                            $productos = $receta->productos;
                            @endphp
                            <td style="border: 1px solid #000;
    height: 120px;
    vertical-align: top;
    word-break: break-word;
    overflow-wrap: break-word;
    overflow: hidden;
    padding: 2px 2px 2px 10px;"
                                colspan="4">

                                @for ($i = 0; $i < 3; $i++)
                                    @if (isset($productos[$i]))
                                    @php
                                    $dosificacion=$productos[$i]->dosificacion;
                                    $texto = $dosificacion->dosificacion_aplicacion ?? '-';
                                    $partes = explode('//', $texto);

                                    if (count($partes) > 1) {
                                    $cuerpo1 = trim($partes[0]);
                                    $cuerpo2 = trim($partes[1]);
                                    } else {
                                    $cuerpo1 = $cuerpo2 = trim($texto);
                                    }
                                    @endphp
                                    <br>
                                    <strong>{{ $i + 1 }})</strong>
                                    {{ $cuerpo2 ?? '-' }}<br>
                                    @else
                                    <strong>{{ $i + 1 }})</strong> -<br>
                                    @endif
                                    @endfor
                            </td>


                            <tr>
                                <td style="border: 1px solid #000; padding: 4px; height: 80px; text-align: center; vertical-align: middle;"
                                    colspan="4">
                                    <div style="display: inline-block; text-align: center;">
                                        @if (isset($qrImage))
                                        <img src="data:image/png;base64,{{ $qrImage }}"
                                            style="width: 60px; height: 60px;"><br>
                                        @else
                                        <img src="{{ public_path('firma-qr.png') }}"
                                            style="width: 60px; height: 60px;"><br>
                                        @endif
                                        @if (isset($qrImage))
                                        <div style="font-size: 10px;">
                                            <strong>{{ $recetaLote->tecnico->tecnico_nombre }}
                                                {{ $recetaLote->tecnico->tecnico_apellido }}</strong><br>
                                            {{ $recetaLote->tecnico->categoria->tecnico_categoria_nombre ?? 'Sin categoría' }}<br>
                                            Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <p class="text-center"><strong>Cuerpo 2: Propietario del cultivo</strong></p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>