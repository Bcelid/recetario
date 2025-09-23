<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Hoja A4 en 2 partes</title>
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
        }

        /* Mitades de la página */
        .half {
            float: left;
            width: 50%;
            height: 100%;
            box-sizing: border-box;

            padding: 2mm;
        }

        .half:last-child {
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
    @foreach ($recetaLote->recetas as $receta)
        @php
            $cliente = $receta->cliente;
            $fecha = \Carbon\Carbon::parse($receta->fecha_emision);
        @endphp

        <div class="page">
            <!-- Bloque Izquierdo -->
            <div class="half">
                <div class="encabezado-sinborder">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>

                            <!-- Fecha de emisión -->
                            <td style="width: 60%; text-align: center; vertical-align: center;">
                                <table class="table-fecha">
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
                            </td>

                            <!-- Logo Almacén y N° receta -->
                            <td style="width: 40%; text-align: center; vertical-align: top;">
                                <img src="{{ $recetaLote->almacen->almacen_logo
                                    ? public_path('storage/' . $recetaLote->almacen->almacen_logo)
                                    : asset('img/sin_logo.png') }}"
                                    alt="Logo Almacén" style="max-height: 50px; display: block; margin: 0 auto;">
                                <div class="receta-numero">N. {{ $receta->receta_numero }}</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="encabezado-sinborder">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr style="background-color: #e0e0e0; font-weight: bold;">
                            <td style="border: 1px solid #000; padding: 4px;" colspan="2">
                                Información del profesional que prescribe:
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px; width: 30%;">Nombres y apellidos:</td>
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
                    </table>
                </div>



                <div class="encabezado-sinborder">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr style="background-color: #e0e0e0; font-weight: bold;">
                            <td style="border: 1px solid #000; padding: 4px;" colspan="2">
                                Información del Almacen:
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px;">Almacen:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                {{ $recetaLote->almacen->almacen_nombre ?? '' }}
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
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr style="background-color: #e0e0e0; font-weight: bold;">
                            <td style="border: 1px solid #000; padding: 2px;" colspan="4">
                                Prescripcion
                                (Forma farmaceutica, principio activo, concentración, unidad, formulacion; incluir
                                cultivo,
                                plaga, dosis y volumen/area a tratarse)
                            </td>
                        </tr>

                        <!-- Espacio grande fijo para escribir -->
                        <td style="
                        border: 1px solid #000;
                        padding: 2px;
                        height: 60px;
                        vertical-align: top;
                        word-break: break-all;
                        white-space: pre-wrap;
                        overflow-wrap: break-word;
                    "
                            colspan="4">@php
                                $productos = $receta->productos;
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
                                @endphp <br>Cantidad:
                                {{ $detalle->producto_cantidad }}<br>Producto:{{ $producto->producto_nombre }}{{ $producto->producto_concentracion }}{{ $producto->producto_presentacion }}{{ $producto->unidadMedida->unidad_medida_detalle ?? '' }}<br>Formulacion:{{ $formulacion . ' (' . $formulacion_abrev . ')' }}<br>Volumen
                                a tratar:{{ $detalle->producto_cantidad * $dosificacion->dosis }} HA <br>Principo
                                Activos:{{ $principios }}
                            @endforeach
                        </td>



                        <!-- Firma centrada -->
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


            </div>

            <!-- Bloque Derecho -->
            <div class="half">
                <div class="encabezado-sinborder">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>

                            <!-- Fecha de emisión -->
                            <td style="width: 60%; text-align: center; vertical-align: center;">
                                <table class="table-fecha">
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
                            </td>

                            <!-- Logo Almacén y N° receta -->
                            <td style="width: 40%; text-align: center; vertical-align: top;">
                                <img src="{{ $recetaLote->almacen->almacen_logo
                                    ? public_path('storage/' . $recetaLote->almacen->almacen_logo)
                                    : asset('img/sin_logo.png') }}"
                                    alt="Logo Almacén" style="max-height: 50px; display: block; margin: 0 auto;">
                                <div class="receta-numero">N. {{ $receta->receta_numero }}</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="encabezado-sinborder">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr style="background-color: #e0e0e0; font-weight: bold;">
                            <td style="border: 1px solid #000; padding: 2px;" colspan="2">
                                Información del cultivo:
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px; width: 30%;">Cultivo a tratar y area de
                                cultivo
                            </td>
                            @php
                                $productos = $receta->productos;
                            @endphp

                            @foreach ($productos as $detalle)
                                @php

                                    $dosificacion = $detalle->dosificacion;

                                @endphp
                                <td style="border: 1px solid #000; padding: 4px; width: 70%;">
                                    {{ $dosificacion->cultivo->cultivo_nombre ?? '-' }} -
                                    {{ $dosificacion->maleza->maleza_nombre ?? '-' }}
                                </td>
                            @endforeach

                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px;">Nombre del propietario:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                {{ $cliente->cliente_nombre }} {{ $cliente->cliente_apellido }}
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px;">Direccion del propietario:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                {{ $cliente->cliente_direccion }}
                            </td>
                        </tr>
                    </table>
                </div>




                <div class="encabezado-sinborder">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr style="background-color: #e0e0e0; font-weight: bold;">
                            <td style="border: 1px solid #000; padding: 4px;" colspan="4">
                                Indicaciones
                                <br> (Dosis, aplicación, frecuencia):
                            </td>
                        </tr>

                        <!-- Espacio grande fijo para escribir -->
                        <td style="
                        border: 1px solid #000;
                        padding: 2px;
                        height: 60px;
                        vertical-align: top;
                        word-break: break-all;
                        white-space: pre-wrap;
                        overflow-wrap: break-word;
                    "
                            colspan="4">

                            @php
                                $productos = $receta->productos;
                            @endphp

                            @foreach ($productos as $detalle)
                                @php
                                    $dosificacion = $detalle->dosificacion;

                                @endphp
                                <br>{{ $dosificacion->dosificacion_aplicacion ?? '-' }}
                                </tr>
                            @endforeach

                        </td>



                        <!-- Firma centrada -->
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
                    <p class="text-center"><strong>Cuerpo 2: Propietario del animal</strong></p>
                </div>

            </div>
        </div>
    @endforeach
</body>

</html>
