@extends('layouts.app')

@section('title', 'Editar Producto')

@section('styles')
    <style>
        /* Tu CSS del formulario de creación (tabs, tablas) aquí */
    </style>
@endsection

@section('content')
    <div class="container" id="formContainer" style="display: none;">
        <h2>Editar Producto</h2>
        <form id="formProducto">
            @csrf
            @method('PUT')
            <input type="hidden" id="producto_id" value="{{ $producto->producto_id }}">

            {{-- Campos básicos --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label>Tipo de Producto *</label>
                    <select name="tipo_producto" id="tipoProducto" class="form-select" required disabled>
                        <option value="0" {{ $producto->producto_tipo == 0 ? 'selected' : '' }}>Agrícola</option>
                        <option value="1" {{ $producto->producto_tipo == 1 ? 'selected' : '' }}>Veterinario</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Nombre *</label>
                    <input type="text" name="nombre_producto" class="form-control"
                        value="{{ $producto->producto_nombre }}" required>
                </div>
                <div class="col-md-4">
                    <label>Concentración *</label>
                    <input type="text" name="concentracion" class="form-control"
                        value="{{ $producto->producto_concentracion }}" required>
                </div>
                <div class="col-md-4">
    <label class="form-label">Presentación *</label>
    <input type="text" name="presentacion" class="form-control" required pattern="^\d+(\.\d+)?$" inputmode="decimal" placeholder="Ej: 10.5" value="{{ $producto->producto_presentacion }}">
    <div class="invalid-feedback">Ingrese un número decimal válido usando punto (.)</div>
</div>
                <div class="col-md-4">
                    <label>Unidad de Medida *</label>
                    <select name="unidad_medida_id" class="form-select" id="selectUnidad"></select>
                </div>
                <div class="col-md-4">
                    <label>Formulación *</label>
                    <select name="formulacion_id" class="form-select" id="selectFormulacion"></select>
                </div>
                <div class="col-md-4">
                    <label>Unidades por Envase</label>
                    <input type="number" name="cantidad_envase" class="form-control"
                        value="{{ $producto->producto_unidad_en_envase }}">
                </div>
                <div class="col-md-8">
                    <label>Diagnóstico</label>
                    <textarea name="diagnostico" class="form-control">{{ $producto->producto_diagnostico }}</textarea>
                </div>
            </div>

            <hr>

            {{-- Ingredientes Activos --}}
            <div class="mb-4 table-responsive">
                <h5>Ingredientes Activos</h5>
                <table class="table table-bordered" id="tablaIngredientes">
                    <thead>
                        <tr>
                            <th>Ingrediente</th>
                            <th>% Composición</th>
                            <th>Unidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($producto->ingredientes as $ing)
                            <tr>
                                <td>
                                    <select class="form-select select-ingrediente" required>
                                        <option value="{{ $ing->ingrediente_activo_id }}" selected>
                                            {{ $ing->ingredienteActivo->ingrediente_activo_nombre }}</option>
                                    </select>
                                </td>
                                <td><input type="number" class="form-control" value="{{ $ing->cantidad }}" required></td>
                                <td>
                                    <select class="form-select select-unidad" required>
                                        <option value="{{ $ing->unidad_medida_id }}" selected>
                                            {{ $ing->unidadMedida->unidad_medida_detalle ?? '' }}</option>
                                    </select>
                                </td>
                                <td><button type="button" class="btn btn-danger btn-sm btn-remove">Eliminar</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarIngrediente">+ Ingrediente
                    Activo</button>
            </div>

            <hr>

            {{-- Dosificación --}}
            <div class="mb-4 table-responsive">
                <h5>Dosificación</h5>
                <table class="table table-bordered" id="tablaDosificacion">
                    <thead>
                        <tr>
                            <th id="col1">{{ $producto->producto_tipo == 0 ? 'Cultivo' : 'Especie' }}</th>
                            <th id="col2">{{ $producto->producto_tipo == 0 ? 'Maleza' : 'Subespecie' }}</th>
                            @if ($producto->producto_tipo == 0)
                                <th>Dosis</th>
                                <th>Unidad</th>
                            @endif
                            <th>Aplicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($producto->dosificaciones as $d)
                            <tr>
                                <input type="hidden" class="dosificacion-id" value="{{ $d->dosificacion_id }}">
                                @if ($producto->producto_tipo == 0)
                                    <td><select class="form-select select-cultivo">
                                            <option value="{{ $d->cultivo_id }}" selected>
                                                {{ $d->cultivo->cultivo_nombre }}</option>
                                        </select></td>
                                    <td><select class="form-select select-maleza">
                                            <option value="{{ $d->maleza_id }}" selected>{{ $d->maleza->maleza_nombre }}
                                            </option>
                                        </select></td>
                                    <td><input type="text" class="form-control input-dosis" value="{{ $d->dosis }}">
                                    </td>
                                    <td><select class="form-select select-unidad-dosificacion">
                                            <option value="{{ $d->unidad_medida_dosificacion_id }}" selected>
                                                {{ $d->unidadMedidaDosificacion->unidad_medida_dosificacion_representacion ?? '' }}
                                            </option>
                                        </select></td>
                                @else
                                    <td><select class="form-select select-especie">
                                            <option value="{{ $d->subespecie->especie->especie_id }}" selected>
                                                {{ $d->subespecie->especie->especie_nombre }}</option>
                                        </select></td>
                                    <td><select class="form-select select-subespecie">
                                            <option value="{{ $d->subespecie_id }}" selected>
                                                {{ $d->subespecie->subespecie_nombre }}</option>
                                        </select></td>
                                @endif
                                <td><input type="text" class="form-control input-aplicacion"
                                        value="{{ $d->dosificacion_aplicacion }}"></td>
                                <td><button type="button" class="btn btn-danger btn-sm btn-remove">Eliminar</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarDosis">+ Dosificación</button>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Actualizar Producto</button>
                <a href="{{ route('product.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>

    </div>

    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content d-flex justify-content-center align-items-center" style="height: 150px;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <div class="ms-3">Cargando, por favor espere...</div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#loadingModal').modal('show');
            const routeToIndex = "{{ route('product.index') }}";
            let tipoActual = $('#tipoProducto').val();

            // Fase 1: Cargar todos los selects
            function cargarSelects() {
                const promesas = [];

                promesas.push(cargarSelect('/unidad-medida', '.select-unidad, #selectUnidad',
                    'unidad_medida_detalle'));
                promesas.push(cargarSelect('/formulacion', '#selectFormulacion', item =>
                    `${item.formulacion_abreviatura} - ${item.formulacion_nombre}`));
                promesas.push(cargarSelect('/ingredientes-activos', '.select-ingrediente',
                    'ingrediente_activo_nombre'));
                promesas.push(cargarSelect('/unidad-medida', '.select-unidad', 'unidad_medida_detalle'));

                if (tipoActual == '0') {
                    promesas.push(cargarSelect('/cultivos', '.select-cultivo', 'cultivo_nombre'));
                    promesas.push(cargarSelect('/maleza', '.select-maleza', 'maleza_nombre'));
                    promesas.push(cargarSelect('/unidad-medida-dosificacion', '.select-unidad-dosificacion',
                        'unidad_medida_dosificacion_representacion'));
                } else {
                    promesas.push(cargarSelect('/especie', '.select-especie', 'especie_nombre'));
                }

                return $.when(...promesas);
            }

            // Fase 2: Rellenar datos del producto en el formulario
            function rellenarDatosProducto() {
                $('#tipoProducto').val(tipoActual).trigger('change');
                $('#selectUnidad').val("{{ $producto->unidad_medida_id }}");
                $('#selectFormulacion').val("{{ $producto->formulacion_id }}");

                // Ingredientes
                $('#tablaIngredientes tbody tr').each(function(index) {
                    const row = $(this);
                    const ingrediente = @json($producto->ingredientes);
                    row.find('.select-ingrediente').val(ingrediente[index]?.ingrediente_activo_id);
                    row.find('.select-unidad').val(ingrediente[index]?.unidad_medida_id);
                });

                // Dosificaciones
                $('#tablaDosificacion tbody tr').each(function(index) {
                    const row = $(this);
                    const dosif = @json($producto->dosificaciones);
                    const item = dosif[index];
                    if (tipoActual == '0') {
                        row.find('.select-cultivo').val(item.cultivo_id);
                        row.find('.select-maleza').val(item.maleza_id);
                        row.find('.select-unidad-dosificacion').val(item.unidad_medida_dosificacion_id);
                    } else {
                        row.find('.select-especie').val(item.subespecie.especie_id).trigger('change');
                        const $sub = row.find('.select-subespecie');
                        $.get(`/subespecie/especie/${item.subespecie.especie_id}`, data => {
                            $sub.empty();
                            data.forEach(sub => $sub.append(new Option(sub.subespecie_nombre, sub
                                .subespecie_id)));
                            $sub.val(item.subespecie_id);
                        });
                    }
                });
            }

            // Fase 3: Eventos
            function inicializarEventos() {
                $('#tipoProducto').change(function() {
                    tipoActual = $(this).val();
                    $('#tablaDosificacion tbody').empty();
                    $('#col1').text(tipoActual == '0' ? 'Cultivo' : 'Especie');
                    $('#col2').text(tipoActual == '0' ? 'Maleza' : 'Subespecie');
                });

                $('#btnAgregarIngrediente').click(function() {
                    let row = `<tr>
                <td><select class="form-select select-ingrediente"></select></td>
                <td><input type="number" class="form-control" placeholder="% Composición" required></td>
                <td><select class="form-select select-unidad"></select></td>
                <td><button type="button" class="btn btn-danger btn-sm btn-remove">Eliminar</button></td>
            </tr>`;
                    $('#tablaIngredientes tbody').append(row);
                    cargarSelect('/ingredientes-activos', '.select-ingrediente:last',
                        'ingrediente_activo_nombre');
                    cargarSelect('/unidad-medida', '.select-unidad:last', 'unidad_medida_detalle');
                });

                $('#btnAgregarDosis').click(function() {
                    let row = tipoActual == '0' ? `
<tr>
    <td><select class="form-select select-cultivo"></select></td>
    <td><select class="form-select select-maleza"></select></td>
    <td><input class="form-control input-dosis" required></td>
    <td><select class="form-select select-unidad-dosificacion"></select></td>
    <td><input class="form-control input-aplicacion" required></td>
    <td><button type="button" class="btn btn-danger btn-sm btn-remove">Eliminar</button></td>
</tr>` : `
<tr>
    <td><select class="form-select select-especie"></select></td>
    <td><select class="form-select select-subespecie"></select></td>
    <td><input class="form-control input-aplicacion" required></td>
    <td><button type="button" class="btn btn-danger btn-sm btn-remove">Eliminar</button></td>
</tr>`;

                    $('#tablaDosificacion tbody').append(row);

                    if (tipoActual == '0') {
                        cargarSelect('/cultivos', '.select-cultivo:last', 'cultivo_nombre');
                        cargarSelect('/maleza', '.select-maleza:last', 'maleza_nombre');
                        cargarSelect('/unidad-medida-dosificacion', '.select-unidad-dosificacion:last',
                            'unidad_medida_dosificacion_representacion');
                    } else {
                        cargarSelect('/especie', '.select-especie:last', 'especie_nombre');
                        $('.select-especie:last').change(function() {
                            let id = $(this).val();
                            const $sub = $(this).closest('tr').find('.select-subespecie');
                            $sub.empty();
                            $.get(`/subespecie/especie/${id}`, data => {
                                data.forEach(i => $sub.append(new Option(i
                                    .subespecie_nombre, i.subespecie_id)));
                            });
                        });
                    }
                });

                $(document).on('change', '.select-especie', function() {
                    const especieId = $(this).val();
                    const $sub = $(this).closest('tr').find('.select-subespecie');

                    if (!especieId) {
                        $sub.empty().append(new Option('Seleccione', ''));
                        return;
                    }

                    $.get(`/subespecie/especie/${especieId}`, function(data) {
                        $sub.empty().append(new Option('Seleccione', ''));
                        data.forEach(sub => {
                            $sub.append(new Option(sub.subespecie_nombre, sub
                                .subespecie_id));
                        });
                    });
                });

                $(document).on('click', '.btn-remove', function() {
                    $(this).closest('tr').remove();
                });

                $('#formProducto').submit(function(e) {
                    e.preventDefault();
                    const id = $('#producto_id').val();
                    const data = {
                        tipo_producto: tipoActual,
                        nombre_producto: $('input[name="nombre_producto"]').val(),
                        concentracion: $('input[name="concentracion"]').val(),
                        presentacion: $('input[name="presentacion"]').val(),
                        unidad_medida_id: $('#selectUnidad').val(),
                        formulacion_id: $('#selectFormulacion').val(),
                        cantidad_envase: $('input[name="cantidad_envase"]').val(),
                        diagnostico: $('textarea[name="diagnostico"]').val(),
                        ingredientes: [],
                        dosificaciones: []
                    };
                    $('#tablaIngredientes tbody tr').each(function() {
                        data.ingredientes.push({
                            ingrediente_id: $(this).find('.select-ingrediente').val(),
                            porcentaje: $(this).find('input').val(),
                            unidad_id: $(this).find('.select-unidad').val()
                        });
                    });
                    // Dosificaciones
                    $('#tablaDosificacion tbody tr').each(function() {
                        const tipo = $('#tipoProducto').val();
                        let item = {
                            dosificacion_id: $(this).find('.dosificacion-id')
                        .val(), // 👈 importante
                            aplicacion: $(this).find('.input-aplicacion').val()
                        };

                        if (tipo === '0') {
                            item.cultivo_id = $(this).find('.select-cultivo').val();
                            item.maleza_id = $(this).find('.select-maleza').val();
                            item.dosis = $(this).find('.input-dosis').val();
                            item.unidad_dosificacion_id = $(this).find(
                                '.select-unidad-dosificacion').val();
                        } else {
                            item.subespecie_id = $(this).find('.select-subespecie').val();
                        }

                        data.dosificaciones.push(item);
                    });



                    $.ajax({
                        url: `/producto/${id}`,
                        method: 'PUT',
                        data: JSON.stringify(data),
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: res => {
                            alert(res.message);
                            window.location.href = routeToIndex;
                        },
                        error: err => {
                            alert('Error al actualizar');
                            console.error(err.responseJSON);
                        }
                    });
                });
            }

            // Función reutilizable para llenar selects
            function cargarSelect(url, selector, labelFn) {
                const $s = $(selector);
                return $.get(url).then(data => {
                    $s.empty().append(new Option('Seleccione', ''));
                    data.forEach(item => {
                        const label = typeof labelFn === 'function' ? labelFn(item) : item[labelFn];
                        const idKey = Object.keys(item).find(k => k.endsWith('_id')) || 'id';
                        const id = item[idKey] || item.id;
                        $s.append(new Option(label, id));
                    });
                });
            }

            // Iniciar en orden
            cargarSelects().done(function() {
                rellenarDatosProducto();
                inicializarEventos();
                $('#loadingModal').modal('hide');
                $('#formContainer').fadeIn(); // o .show() si no quieres animación
            });
        });
    </script>
@endsection
