@extends('layouts.app')

@section('title', 'Reporte de productos recetados')

@section('styles')
    <style>
        .report-summary-card {
            border: 0;
            border-left: 4px solid #13854A;
        }

        .report-table th,
        .report-table td {
            vertical-align: middle;
        }

        .day-heading {
            background: #e8f5ee;
            border-left: 4px solid #13854A;
        }

        .filter-group {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: .65rem;
            height: 100%;
            padding: 1rem;
        }

        .filter-group-title {
            color: #334155;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .04em;
            margin-bottom: .85rem;
            text-transform: uppercase;
        }

        .filter-group-title i {
            color: #13854A;
            margin-right: .35rem;
        }

        @media print {
            .app-header,
            .app-sidebar,
            .report-actions,
            .filters-card,
            .app-footer {
                display: none !important;
            }

            .app-main {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                break-inside: avoid;
                box-shadow: none !important;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $formatQuantity = static function ($value) {
            $decimals = floor((float) $value) == (float) $value ? 0 : 2;
            return number_format((float) $value, $decimals, ',', '.');
        };
    @endphp

    <div class="container-fluid">
        <div class="mb-4">
            <div>
                <h1 class="h3 mb-1">Reporte de productos recetados</h1>
                <p class="text-muted mb-0">Totales por producto y detalle diario según la fecha de emisión.</p>
            </div>
        </div>

        <div class="card filters-card shadow-sm mb-4">
            <div class="card-header bg-white">
                <div class="fw-semibold">Filtros del reporte</div>
                <small class="text-muted">Selecciona el período y luego acota los resultados si lo necesitas.</small>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.prescriptions.index') }}" class="row g-3">
                    <div class="col-xl-4">
                        <div class="filter-group">
                            <div class="filter-group-title">
                                <i class="fa-regular fa-calendar"></i> Período
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="fecha_desde" class="form-label">Desde</label>
                                    <input type="date"
                                        class="form-control @error('fecha_desde') is-invalid @enderror"
                                        id="fecha_desde" name="fecha_desde" value="{{ $filtros['fecha_desde'] }}"
                                        required>
                                    @error('fecha_desde')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="fecha_hasta" class="form-label">Hasta</label>
                                    <input type="date"
                                        class="form-control @error('fecha_hasta') is-invalid @enderror"
                                        id="fecha_hasta" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] }}"
                                        required>
                                    @error('fecha_hasta')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="filter-group">
                            <div class="filter-group-title">
                                <i class="fa-solid fa-location-dot"></i> Ubicación y cliente
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="almacen_id" class="form-label">Almacén</label>
                                    <select class="form-select" id="almacen_id" name="almacen_id">
                                        <option value="">Todos los almacenes</option>
                                        @foreach ($almacenes as $almacen)
                                            <option value="{{ $almacen->almacen_id }}"
                                                @selected((string) ($filtros['almacen_id'] ?? '') === (string) $almacen->almacen_id)>
                                                {{ $almacen->almacen_nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="cliente_id" class="form-label">Cliente</label>
                                    <select class="form-select" id="cliente_id" name="cliente_id"
                                        @disabled(empty($filtros['almacen_id']))>
                                        <option value="">Todos los clientes</option>
                                        @foreach ($clientes as $cliente)
                                            <option value="{{ $cliente->cliente_id }}"
                                                @selected((string) ($filtros['cliente_id'] ?? '') === (string) $cliente->cliente_id)>
                                                {{ trim($cliente->cliente_nombre . ' ' . $cliente->cliente_apellido) }}
                                                ({{ $cliente->cliente_cedula }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3">
                        <div class="filter-group">
                            <div class="filter-group-title">
                                <i class="fa-solid fa-tags"></i> Clasificación
                            </div>
                            <label for="receta_tipo" class="form-label">Tipo de receta</label>
                            <select class="form-select" id="receta_tipo" name="receta_tipo">
                                <option value="">Todos los tipos</option>
                                <option value="0" @selected((string) ($filtros['receta_tipo'] ?? '') === '0')>Agrícola</option>
                                <option value="1" @selected((string) ($filtros['receta_tipo'] ?? '') === '1')>Veterinaria</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 d-flex flex-wrap align-items-center gap-2 report-actions">
                        <button type="submit" class="btn btn-portlogistics">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Generar reporte
                        </button>
                        <a href="{{ route('reports.prescriptions.index') }}" class="btn btn-outline-secondary">
                            Limpiar filtros
                        </a>
                        <div class="vr d-none d-sm-block mx-1"></div>
                        <button type="submit" class="btn btn-outline-danger"
                            formaction="{{ route('reports.prescriptions.pdf') }}" formtarget="_blank">
                            <i class="fa-solid fa-file-pdf me-1"></i> Exportar PDF
                        </button>
                        <button type="submit" class="btn btn-outline-success"
                            formaction="{{ route('reports.prescriptions.excel') }}" formtarget="_blank">
                            <i class="fa-solid fa-file-excel me-1"></i> Exportar Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card report-summary-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Productos diferentes</div>
                        <div class="fs-3 fw-bold">{{ number_format($totales['productos'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card report-summary-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Recetas incluidas</div>
                        <div class="fs-3 fw-bold">{{ number_format($totales['recetas'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card report-summary-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Cantidad total recetada</div>
                        <div class="fs-3 fw-bold">{{ $formatQuantity($totales['cantidad']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Resumen por producto</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover report-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Concentración</th>
                                <th>Presentación</th>
                                <th class="text-end">Recetas</th>
                                <th class="text-end">Cantidad total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($resumen as $producto)
                                <tr>
                                    <td class="fw-semibold">{{ $producto->producto_nombre }}</td>
                                    <td>{{ $producto->producto_concentracion }}</td>
                                    <td>
                                        {{ $formatQuantity($producto->producto_presentacion) }}
                                        {{ $producto->unidad_medida_detalle }}
                                    </td>
                                    <td class="text-end">{{ number_format($producto->total_recetas, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">{{ $formatQuantity($producto->cantidad_total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No se encontraron recetas con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <h2 class="h4 mb-3">Detalle por fecha</h2>
        @forelse ($detalle as $fecha => $productos)
            <div class="card shadow-sm mb-3">
                <div class="card-header day-heading">
                    <span class="fw-semibold text-capitalize">
                        {{ \Carbon\Carbon::parse($fecha)->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover report-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Concentración</th>
                                    <th>Presentación</th>
                                    <th class="text-end">Recetas</th>
                                    <th class="text-end">Cantidad</th>
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
                                        <td class="text-end">{{ number_format($producto->total_recetas, 0, ',', '.') }}</td>
                                        <td class="text-end fw-semibold">{{ $formatQuantity($producto->cantidad_total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-light border text-muted">
                No hay detalle diario para los filtros seleccionados.
            </div>
        @endforelse
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            const $almacen = $('#almacen_id');
            const $cliente = $('#cliente_id');
            const clientesUrl = @json(route('reports.prescriptions.clients', ['almacenId' => '__ALMACEN__']));

            $almacen.select2({
                width: '100%',
                placeholder: 'Todos los almacenes',
                allowClear: true
            });

            $cliente.select2({
                width: '100%',
                placeholder: 'Todos los clientes',
                allowClear: true
            });

            $almacen.on('change', function() {
                const almacenId = $(this).val();

                $cliente.empty().append(new Option('Todos los clientes', '')).val('').trigger('change');

                if (!almacenId) {
                    $cliente.prop('disabled', true);
                    return;
                }

                $cliente.prop('disabled', true);

                $.get(clientesUrl.replace('__ALMACEN__', almacenId))
                    .done(function(response) {
                        response.clientes.forEach(function(cliente) {
                            $cliente.append(new Option(cliente.text, cliente.id));
                        });
                        $cliente.prop('disabled', false);
                    })
                    .fail(function() {
                        alert('No se pudieron cargar los clientes del almacén seleccionado.');
                    });
            });
        });
    </script>
@endsection
