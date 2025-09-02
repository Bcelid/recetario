<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Receta Veterinaria</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .mini {
            min-height: 273mm;
            padding: 8px;
        }

        .header,
        .section,
        .footer {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            background: #d6eaf8;
            margin-bottom: 6px;
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
            font-size: 12px;
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
            $producto = $receta->producto;
            $cliente = $receta->cliente;
            $dosificacion = $receta->dosificacion;
            $subespecie = $dosificacion->subespecie ?? null;
            $especie = $subespecie->especie ?? null;
            $ingredientes = $producto->ingredientes;
        @endphp

        <div class="mini">

            <div class="header">
                <table class="no-border">
                    <tr>
                        <td style="width: 30%; text-align: center;">
                            <img src="{{ public_path('img/AGROCALIDAD.png') }}" class="logo" alt="Logo 1"><br>
                            <img src="{{ public_path('img/logo-veterinaria.jpg') }}" class="logo" alt="Logo 2">
                        </td>
                        <td style="width: 40%; text-align: center;">
                            <strong>REPRESENTACIONES TÉCNICAS PARA ALMACENISTA AGROPECUARIOS</strong><br>
                            MEDICO VETERINARIO - SENESCYT {{ $recetaLote->tecnico->tecnico_senescyt ?? '' }}<br>
                            {{ $recetaLote->tecnico->tecnico_apellido }} {{ $recetaLote->tecnico->tecnico_nombre }} <br>
                            C.I.: {{ $recetaLote->tecnico->tecnido_cedula }}<br>
                            TELÉFONO: {{ $recetaLote->tecnico->tecnico_telefono }}<br>
                            <strong>RECETA VETERINARIA PARA EXPENDIO DE PLAGUICIDAS</strong>
                        </td>
                        <td style="width: 30%; text-align: center;">
                            <div>
                                <img src="{{ $recetaLote->almacen->almacen_logo
                                    ? public_path('storage/' . $recetaLote->almacen->almacen_logo)
                                    : public_path('img/sin_logo.png') }}"
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
                <div class="title">INFORMACIÓN DEL PACIENTE</div>
                <table>
                    <tr>
                        <td style="width: 25%;">ESPECIE:</td>
                        <td style="width: 25%;">{{ $especie->especie_nombre ?? '-' }}</td>
                        <td style="width: 25%;">SUBESPECIE:</td>
                        <td style="width: 25%;">{{ $subespecie->subespecie_nombre ?? '-' }}</td>
                    </tr>
                    @php
                        // Obtener un sexo aleatorio del array
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
                    @endphp
                    <tr>
                        <td style="width: 25%;">SEXO:</td>
                        <td style="width: 25%;">{{ $sexo }}</td>
                        <td style="width: 25%;">EDAD:</td>
                        <td style="width: 25%;">{{ $edad }}</td>
                    </tr>d style="width: 25%;" >{{ $subespecie->subespecie_nombre ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%;">NOMBRE DEL PROPIETARIO:</td>
                        <td style="width: 25%;">{{ $cliente->cliente_nombre }} {{ $cliente->cliente_apellido }}</td>
                        <td style="width: 25%;">DIRECCIÓN DEL PROPIETARIO:</td>
                        <td style="width: 25%;">{{ $cliente->cliente_direccion }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="title">PRESCRIPCIÓN</div>
                <table>
                    <tr>
                        <td><strong>FORMA FARMACÉUTICA:</strong></td>
                        <td colspan="3">
                            {{ $producto->producto_nombre }} {{ $producto->producto_concentracion }}
                            {{ $producto->producto_presentacion }}
                            {{ $producto->unidadMedida->unidad_medida_detalle ?? '' }}
                        </td>

                    </tr>
                    <tr>
                        <td><strong>PRINCIPIO ACTIVO</strong></td>
                        <td colspan="3"><strong>CONCENTRACIÓN</strong></td>
                    </tr>
                    @php
                        $maxFilas = 4;
                        $i = 0;
                    @endphp
                    @foreach ($ingredientes as $ingrediente)
                        <tr>
                            <td>{{ $ingrediente->ingredienteActivo->ingrediente_activo_nombre }}</td>
                            <td colspan="3">{{ $ingrediente->cantidad }}
                                {{ $ingrediente->unidadMedida->unidad_medida_detalle ?? '' }}</td>
                        </tr>
                        @php $i++; @endphp
                    @endforeach
                    @for (; $i < $maxFilas; $i++)
                        <tr>
                            <td>--------</td>
                            <td colspan="3">------</td>
                        </tr>
                    @endfor
                    <tr>
                        <td>NÚMERO DE UNIDADES POR ENVASE:</td>
                        <td>{{ $producto->producto_unidad_en_envase ?? '-' }}</td>
                        <td>ADMINISTRACIÓN:</td>
                        <td>{{ $dosificacion->dosificacion_aplicacion ?? '-' }}</td>
                    </tr>
                </table>
                <p><strong>DIAGNÓSTICO:</strong> {{ $producto->producto_diagnostico ?? '-' }}</p>
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
                                style="width: 100px; height: 100px;"><br>
                        @else
                            <img src="{{ public_path('firma-qr.png') }}" style="width: 100px; height: 60px;"><br>
                        @endif
                        <div style="font-size: 12px;">FIRMA</div>
                    </div>

                    @if (isset($qrImage))
                        <div>
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
