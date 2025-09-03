@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Bienvenido, {{ auth()->user()->name }}</h1>

        {{-- Indicadores --}}
        <div class="row">
            {{-- Lotes no enviados --}}
            <div class="col-md-4 mb-3">
                <div class="card shadow border-0 bg-light text-dark">
                    <div class="card-body d-flex justify-content-between align-items-center py-3 px-4">
                        <div>
                            <h6 class="text-muted mb-1">Lotes no enviados</h6>
                            <h2 class="mb-0 fw-bold" id="no-enviados-count">{{ $noEnviadosCount }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-exclamation-circle text-warning display-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lotes enviados --}}
            <div class="col-md-4 mb-3">
                <div class="card shadow border-0 bg-light text-dark">
                    <div class="card-body d-flex justify-content-between align-items-center py-3 px-4">
                        <div>
                            <h6 class="text-muted mb-1">Lotes enviados</h6>
                            <h2 class="mb-0 fw-bold" id="enviados-count">{{ $enviadosCount }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-check-circle text-success display-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Puedes agregar más indicadores aquí --}}
        </div>


        {{-- Filtros --}}
        <div class="card mb-4">
            <div class="card-body">
                <form id="filters-form" class="row g-3">
                    <div class="col-md-2">
                        <label for="tipo_lote" class="form-label">Tipo de Lote</label>
                        <select class="form-select" id="tipo_lote" name="tipo_lote">
                            <option value="">Todos</option>
                            <option value="0">Agrícola</option>
                            <option value="1">Veterinario</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="enviados">Enviados</option>
                            <option value="no_enviados" selected>No enviados</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="almacen_id" class="form-label">Almacén</label>
                        <select class="form-select" id="almacen_id" name="almacen_id">
                            <option value="">Todos</option>
                            @foreach (App\Models\Almacen::orderBy('almacen_nombre')->get() as $almacen)
                                <option value="{{ $almacen->almacen_id }}">{{ $almacen->almacen_nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_min" class="form-label">Desde</label>
                        <input type="date" class="form-control" id="fecha_min" name="fecha_min">
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_max" class="form-label">Hasta</label>
                        <input type="date" class="form-control" id="fecha_max" name="fecha_max">
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla dinámica --}}
        <div class="card">
            <div class="card-body">
                <table id="lotes-table" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Almacén</th>
                            <th>Tipo</th>
                            <th>Fecha Creación</th>
                            <th>Estado Envío</th>
                            <th>Fecha Envío</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables llenará esto --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#lotes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('dashboard.data') }}',
                    data: function(d) {
                        d.tipo_lote = $('#tipo_lote').val();
                        d.estado = $('#estado').val();
                        d.fecha_min = $('#fecha_min').val();
                        d.fecha_max = $('#fecha_max').val();
                        d.almacen_id = $('#almacen_id').val();
                    }
                },
                columns: [{
                        data: 'receta_lote_id',
                        name: 'receta_lote_id',
                        title: 'Lote #'
                    },
                    {
                        data: 'almacen',
                        name: 'almacen',
                        title: 'Almacén'
                    },
                    {
                        data: 'receta_tipo',
                        name: 'receta_tipo'
                    },
                    {
                        data: 'fecha_creacion',
                        name: 'fecha_creacion'
                    },
                    {
                        data: 'estado_envio',
                        name: 'estado_envio'
                    },
                    {
                        data: 'receta_lote_fecha_envio',
                        name: 'receta_lote_fecha_envio'
                    },
                ],
                order: [
                    [0, 'desc']
                ]
            });

            function reloadIndicators() {
                $.ajax({
                    url: '{{ route('dashboard.counts') }}',
                    data: {
                        tipo_lote: $('#tipo_lote').val(),
                        estado: $('#estado').val(),
                        fecha_min: $('#fecha_min').val(),
                        fecha_max: $('#fecha_max').val(),
                        almacen_id: $('#almacen_id').val()
                    },
                    success: function(data) {
                        $('#enviados-count').text(data.enviados);
                        $('#no-enviados-count').text(data.no_enviados);
                    },
                    error: function() {
                        console.error('Error al cargar los indicadores.');
                    }
                });
            }


            // Recargar tabla al cambiar filtros
            $('#filters-form select, #filters-form input').on('change', function() {
                table.ajax.reload();
                reloadIndicators();
            });
        });
    </script>
@endsection
