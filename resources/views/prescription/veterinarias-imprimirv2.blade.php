<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Receta Veterinaria</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 8pt;
        }

        /* Página A4 en mm (landscape) */
        .page {
            width: 270mm;
            height: 185mm;
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
            font-size: 10px;
        }

        .table-fecha td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
        }

        .receta-numero {
            margin-top: 5px;
            padding: 3px;
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
            margin-bottom: 5px;
        }

        .encabezado-sinborder {
            width: 100%;
            padding-bottom: 5px;
            margin-bottom: 5px;
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
                                            RECETA VETERINARIA
                                        </h4>
                                    </td>

                                    <td style="width: 40%; text-align: center; vertical-align: top;">
                                        <img src="{{ $recetaLote->almacen->almacen_logo
                                            ? url('storage/' . $recetaLote->almacen->almacen_logo)
                                            : url('img/sin_logo.png') }}"
                                            alt="Logo Almacén"
                                            style="max-height: 50px; display: block; margin: 0 auto;">
                                        <div class="receta-numero">N. {{ $receta->receta_numero }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="encabezado-sinborder">
                            <table style="width: 100%; border-collapse: collapse; font-size:8pt;">
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
                            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                                <tr style="background-color: #e0e0e0; font-weight: bold;">
                                    <td style="border: 1px solid #000; padding: 2px;" colspan="4">
                                        Prescripción
                                        (Forma farmaceutica, principio activo, concentración, numero de unidades por
                                        envase)
                                    </td>
                                </tr>

                                @php
                                    $productos = $receta->productos;
                                @endphp
                                <td style="
                                    border: 1px solid #000;
                                    height: 70px;
                                    vertical-align: top;
                                    word-break: break-word;
                                    overflow-wrap: break-word;
                                    overflow: hidden;
                                    padding: 2px 2px 2px 10px;
                                    line-height: 1.2; /* control del interlineado */
                                " colspan="4">
                                    @for ($i = 0; $i < 3; $i++)
                                        @if (isset($productos[$i]))
                                            @php
                                                $detalle = $productos[$i];
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
                                                    
                                                    $cantidad = fmod($detalle->producto_cantidad, 1) == 0
                                        ? number_format($detalle->producto_cantidad, 0)
                                        : number_format($detalle->producto_cantidad, 1);
                                
                                    // Redondear presentación
                                    $presentacion = fmod($producto->producto_presentacion, 1) == 0
                                        ? number_format($producto->producto_presentacion, 0)
                                        : number_format($producto->producto_presentacion, 1);
                                            @endphp
                                
                                            <div style="line-height: 1.2; margin: 0 0 2px 0;">
                                                <strong>{{ $i + 1 }})</strong>
                                                {{ $cantidad }}
                                                {{ $producto->producto_nombre }}
                                                {{ $producto->producto_concentracion }}
                                                {{ $presentacion }}{{ $producto->unidadMedida->unidad_medida_detalle ?? '' }}
                                                ({{ $formulacion_abrev }})
                                                U.envase: {{ $producto->producto_unidad_en_envase ?? '-' }}
                                                Principio(s) Activo(s): {{ $principios }}
                                            </div>
                                        @else
                                            <div style="line-height: 1.2; margin: 0 0 2px 0;">
                                                <strong>{{ $i + 1 }})</strong> -
                                            </div>
                                        @endif
                                    @endfor
                                </td>


                                <tr style="background-color: #e0e0e0; font-weight: bold;">
                                    <td style="border: 1px solid #000; padding: 2px;" colspan="4">
                                        Diagnóstico
                                    </td>
                                </tr>

                                @php
                                    $productos = $receta->productos;
                                @endphp
                                <td style="
                                    border: 1px solid #000;
                                    height: 70px;
                                    vertical-align: top;
                                    word-break: break-word;
                                    overflow-wrap: break-word;
                                    overflow: hidden;
                                    padding: 2px 2px 2px 10px;
                                    line-height: 1.2; /* Ajusta la altura de línea */
                                " colspan="4">
                                    @for ($i = 0; $i < 3; $i++)
                                    @if (isset($productos[$i]))
                                        @php
                                            $detalle = $productos[$i];
                                            $producto = $detalle->producto;
                                        @endphp
                                        <div style="line-height: 1.2; margin: 0;">
                                            <strong>{{ $i + 1 }})</strong>
                                            {{ $producto->producto_diagnostico ?? '-' }}
                                        </div>
                                    @else
                                        <div style="line-height: 1.2; margin: 0;">
                                            <strong>{{ $i + 1 }})</strong> -
                                        </div>
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
                                            RECETA VETERINARIA
                                        </h4>
                                    </td>

                                    <td style="width: 40%; text-align: center; vertical-align: top;">
                                        <img src="{{ $recetaLote->almacen->almacen_logo
                                            ? url('storage/' . $recetaLote->almacen->almacen_logo)
                                            : url('img/sin_logo.png') }}"
                                            alt="Logo Almacén"
                                            style="max-height: 50px; display: block; margin: 0 auto;">
                                        <div class="receta-numero">N. {{ $receta->receta_numero }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="encabezado-sinborder">
                            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                                <tr style="background-color: #e0e0e0; font-weight: bold;">
                                    <td style="border: 1px solid #000; padding: 2px;" colspan="2">
                                        Información del paciente:
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #000; padding: 4px; width: 30%;">Especie</td>
                                    @php
                                        $productos = $receta->productos;
                                    @endphp
                                    <td style="border: 1px solid #000; padding: 4px; width: 70%;">
                                        @for ($i = 0; $i < 3; $i++)
                                            @if (isset($productos[$i]))
                                                @php
                                                    $dosificacion = $productos[$i]->dosificacion;
                                                    $subespecie = $dosificacion->subespecie ?? null;
                                                    $especie = $subespecie->especie->especie_nombre ?? null;
                                                @endphp
                                                <strong>{{ $i + 1 }})</strong> {{ $especie }}<br>
                                            @else
                                                <strong>{{ $i + 1 }})</strong> -<br>
                                            @endif
                                        @endfor
                                    </td>

                                </tr>
                                <tr>
                                    <td style="border: 1px solid #000; padding: 4px; width: 30%;">Nombre/Identificación
                                    </td>
                                    @php
                                        $productos = $receta->productos;
                                    @endphp
                                    <td style="border: 1px solid #000; padding: 4px; width: 70%;">
                                        @for ($i = 0; $i < 3; $i++)
                                            @if (isset($productos[$i]))
                                                @php
                                                    $dosificacion = $productos[$i]->dosificacion;
                                                    $subespecie = $dosificacion->subespecie->subespecie_nombre ?? null;
                                                    $especie = $subespecie->especie ?? null;
                                                @endphp
                                                <strong>{{ $i + 1 }})</strong> {{ $subespecie }}<br>
                                            @else
                                                <strong>{{ $i + 1 }})</strong> -<br>
                                            @endif
                                        @endfor
                                    </td>

                                </tr>
                                <tr>
                                    <td style="border: 1px solid #000; padding: 4px; width: 30%;">Edad / Sexo</td>
                                    @php
                                        $productos = $receta->productos;
                                    @endphp
                                    <td style="border: 1px solid #000; padding: 4px; width: 70%;">
                                        @for ($i = 0; $i < 3; $i++)
                                            @if (isset($productos[$i]))
                                                @php
                                                    $dosificacion = $productos[$i]->dosificacion;
                                                    $subespecie = $dosificacion->subespecie ?? null;

                                                    // Calcular edad
                                                    $edad = '-';
                                                    if (
                                                        is_numeric($subespecie->edad_min ?? null) &&
                                                        is_numeric($subespecie->edad_max ?? null) &&
                                                        $subespecie->edad_min <= $subespecie->edad_max
                                                    ) {
                                                        $randomEdad = rand(
                                                            $subespecie->edad_min,
                                                            $subespecie->edad_max,
                                                        );
                                                        $edad =
                                                            $randomEdad .
                                                            ' ' .
                                                            strtoupper($subespecie->unidad_edad ?? '');
                                                    }

                                                    // Calcular sexo
                                                    $sexo = '-';
                                                    if (
                                                        is_array($subespecie->sexos ?? null) &&
                                                        count($subespecie->sexos) > 0
                                                    ) {
                                                        $sexo = strtoupper(
                                                            $subespecie->sexos[array_rand($subespecie->sexos)],
                                                        );
                                                    }
                                                @endphp
                                                <strong>{{ $i + 1 }})</strong>
                                                Edad: {{ $edad }} | Sexo: {{ $sexo }}<br>
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
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr style="background-color: #e0e0e0; font-weight: bold;">
                    <td style="border: 1px solid #000; padding: 4px;" colspan="4">
                        Posologia/instrucción para el paciente:
                        <br>(Vía de administracion, unidad a administrar por unidad de tiempo, duración del tratamiento):
                    </td>
                </tr>

                @php
                    $productos = $receta->productos;
                @endphp
                <td style="border: 1px solid #000;
    height: 70px;
    vertical-align: top;
    word-break: break-word;
    overflow-wrap: break-word;
    overflow: hidden;
    padding: 2px 2px 2px 10px;
    line-height: 1.2;" 
    colspan="4">

    @for ($i = 0; $i < 3; $i++)
        @if (isset($productos[$i]))
            @php
                $dosificacion = $productos[$i]->dosificacion;
            @endphp
            <div style="line-height: 1.2; margin: 0 0 2px 0;">
                <strong>{{ $i + 1 }})</strong>
                {{ $dosificacion->dosificacion_aplicacion ?? '-' }}
            </div>
        @else
            <div style="line-height: 1.2; margin: 0 0 2px 0;">
                <strong>{{ $i + 1 }})</strong> -
            </div>
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
                                <img src="{{ public_path('firma-qr.png') }}" style="width: 60px; height: 60px;"><br>
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
        </td>
        </tr>
        </table>
        </div>
</body>

</html>