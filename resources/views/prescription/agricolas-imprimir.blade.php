<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recetas Agrícolas</title>
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
            padding: 7px;
            margin-bottom: 10px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
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
            margin-top: 5px;
            object-fit: contain;
        }
    </style>
</head>

<body>

    @foreach ($recetaLote->recetas as $receta)
        @php
            $producto = $receta->producto;
            $dosificacion = $receta->dosificacion;
            $cliente = $receta->cliente;
            $formulacion = $producto->formulacion->formulacion_nombre ?? '';
            $formulacion_abrev = $producto->formulacion->formulacion_abreviatura ?? '';
            $ingredientes = $producto->ingredientes;
        @endphp

        <div class="mini">
            <div class="header">
                <table class="no-border">
                    <tr>
                        <td style="width: 30%; text-align: center;">
                            <img src="{{ public_path('img/AGROCALIDAD.png') }}" class="logo" alt="Logo 1"><br>
                            <img src="{{ public_path('img/logo-agropecuario.jpg') }}" class="logo" alt="Logo 2">
                        </td>
                        <td style="width: 40%; text-align: center;">
                            <strong>REPRESENTACIONES TÉCNICAS PARA ALMACENISTA AGROPECUARIOS</strong><br>
                            ING. AGRÓNOMO - SENESCYT {{ $recetaLote->tecnico->tecnico_senescyt ?? '' }}<br>
                            {{ $recetaLote->tecnico->tecnico_apellido }} {{ $recetaLote->tecnico->tecnico_nombre }} <br>
                            C.I.: {{ $recetaLote->tecnico->tecnido_cedula }}<br>
                            TELÉFONO: {{ $recetaLote->tecnico->tecnico_telefono }}
                            <strong>RECETA AGRICOLA PARA EXPENDIO DE PLAGUICIDAS</strong>
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
                    <tr>
                        <td style="width: 25%;">NOMBRE COMERCIAL:</td>
                        <td style="width: 35%;">{{ $producto->producto_nombre }}</td>
                        <td style="width: 20%;">CANTIDAD:</td>
                        <td style="width: 20%;">{{ $receta->producto_cantidad }}</td>
                    </tr>
                    <tr>
                        <td>FORMULACIÓN:</td>
                        <td>{{ $formulacion . ' (' . $formulacion_abrev . ')' }}</td>
                        <td>VOLUMEN A TRATARSE:</td>
                        <td>{{ $receta->producto_cantidad * $dosificacion->dosis }} HA</td>
                    </tr>
                    <tr>
                        <td>CULTIVO:</td>
                        <td>{{ $dosificacion->cultivo->cultivo_nombre ?? '-' }}</td>
                        <td>PLAGA:</td>
                        <td>{{ $dosificacion->maleza->maleza_nombre ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <table>
                                <tr>
                                    <th>PRINCIPIO ACTIVO</th>
                                    <th>CONCENTRACIÓN</th>
                                </tr>

                                @php
                                    $totalFilas = 4;
                                    $contador = 0;
                                @endphp

                                @foreach ($ingredientes as $ingrediente)
                                    <tr>
                                        <td>{{ $ingrediente->ingredienteActivo->ingrediente_activo_nombre }}</td>
                                        <td>{{ $ingrediente->cantidad }}
                                            {{ $ingrediente->unidadMedida->unidad_medida_detalle ?? '' }}</td>
                                    </tr>
                                    @php $contador++; @endphp
                                @endforeach

                                @for ($i = $contador; $i < $totalFilas; $i++)
                                    <tr>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                @endfor
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td>RECOMENDACIÓN:</td>
                        <td colspan="3">{{ $dosificacion->dosificacion_aplicacion ?? '' }}</td>
                    </tr>
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
                        <img src="{{ public_path('firma-qr.png') }}" style="width: 100px; height: 60px;"><br>
                        <div style="font-size: 12px;">FIRMA</div>
                    </div>
                </div>
            </div>

        </div>
    @endforeach

</body>

</html>
