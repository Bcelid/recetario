@extends('layouts.app')

@section('title', 'Registro de Producto')

@section('styles')
    <style>
        table th,
        table td {
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
        }

        #tablaIngredientes th:nth-child(1),
        #tablaIngredientes td:nth-child(1),
        #tablaDosificacion th:nth-child(1),
        #tablaDosificacion td:nth-child(1) {
            width: 200px;
        }

        #tablaIngredientes th:nth-child(2),
        #tablaIngredientes td:nth-child(2),
        #tablaDosificacion th:nth-child(2),
        #tablaDosificacion td:nth-child(2) {
            width: 200px;
        }

        #tablaIngredientes th:nth-child(3),
        #tablaIngredientes td:nth-child(3),
        #tablaDosificacion th:nth-child(3),
        #tablaDosificacion td:nth-child(3) {
            width: 100px;
        }

        #tablaIngredientes th:nth-child(4),
        #tablaIngredientes td:nth-child(4),
        #tablaDosificacion th:nth-child(4),
        #tablaDosificacion td:nth-child(4) {
            width: 100px;
        }

        #tablaDosificacion th:nth-child(5),
        #tablaDosificacion td:nth-child(5) {
            width: 200px;
        }

        #tablaDosificacion th:nth-child(6),
        #tablaDosificacion td:nth-child(6) {
            width: 100px;
        }
    </style>
@endsection


@section('content')
    <div class="container">
        <h2 class="mb-4">Registrar Producto</h2>

        <form id="formProducto" style="display: none;">
            @csrf
            <div class="row g-3">
                <!-- Tipo de producto (0 agrícola, 1 veterinario) -->
                <div class="col-md-4">
                    <label class="form-label">Tipo de Producto *</label>
                    <select name="tipo_producto" id="tipoProducto" class="form-select" required>
                        <option value="" disabled selected>Seleccione tipo</option>
                        <option value="0">Agrícola</option>
                        <option value="1">Veterinario</option>
                    </select>
                </div>
                <!-- Nombre del producto -->
                <div class="col-md-4">
                    <label class="form-label">Nombre del producto *</label>
                    <input type="text" name="nombre_producto" class="form-control" required>
                </div>

                <!-- Concentración -->
                <div class="col-md-4">
                    <label class="form-label">Concentración *</label>
                    <input type="text" name="concentracion" class="form-control" required>
                </div>

                <!-- Presentación -->
                <div class="col-md-4">
                    <label class="form-label">Presentación *</label>
                    <input type="text" name="presentacion" class="form-control" required pattern="^\d+(\.\d+)?$"
                        inputmode="decimal" placeholder="Ej: 10.5">
                    <div class="invalid-feedback">Ingrese un número decimal válido usando punto (.)</div>
                </div>


                <!-- Unidad de medida (ajax) -->
                <div class="col-md-4">
                    <label class="form-label">Unidad de Medida *</label>
                    <select name="unidad_medida_id" class="form-select" id="selectUnidad"></select>
                </div>

                <!-- Formulación (ajax) -->
                <div class="col-md-4">
                    <label class="form-label">Formulación *</label>
                    <select name="formulacion_id" class="form-select" id="selectFormulacion"></select>
                </div>

                <!-- Cantidad por envase -->
                <div class="col-md-4">
                    <label class="form-label">Unidades por Envase (opcional)</label>
                    <input type="number" name="cantidad_envase" class="form-control" min="0">
                </div>

                <!-- Diagnóstico (opcional) -->
                <div class="col-md-4">
                    <label class="form-label">Diagnóstico (opcional)</label>
                    <textarea name="diagnostico" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <hr>

            <!-- Ingredientes activos (sección AJAX sin select2) -->
            <div class="mb-4 table-responsive">
                <h5>Ingredientes Activos</h5>
                <table class="table table-bordered" id="tablaIngredientes">
                    <thead>
                        <tr>
                            <th>Ingrediente Activo</th>
                            <th>% Composición</th>
                            <th>Unidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarIngrediente">+ Ingrediente
                    Activo</button>
            </div>

            <hr>

            <!-- Dosificación dinámica según tipo de producto -->
            <div class="mb-4 table-responsive">
                <h5>Dosificación</h5>
                <table class="table table-bordered" id="tablaDosificacion">
                    <thead>
                        <tr>
                            <th id="col1">Especie / Cultivo</th>
                            <th id="col2">Subespecie / Plaga</th>
                            <th class="dosis-col">Dosis</th>
                            <th class="dosis-col">Unidad</th>
                            <th>Aplicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarDosis">+ Dosificación</button>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Guardar Producto</button>
            </div>
        </form>

        

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#loadingModal').modal('show');
            const routeToIndex = "{{ route('product.index') }}";

            // Variables globales
            let tipoActual = null;

            // Ocultar tabla de dosificación al inicio
            $('#tablaDosificacion').closest('div').hide();

            // Cargar combos base (unidad de medida y formulación)
            cargarSelect('/unidad-medida', '#selectUnidad', 'unidad_medida_detalle');
            cargarSelect('/formulacion', '#selectFormulacion', item =>
                `${item.formulacion_abreviatura} - ${item.formulacion_nombre}`);

            // Al cambiar tipo de producto
            $('#tipoProducto').on('change', function() {
                tipoActual = $(this).val();
                $('#tablaDosificacion').closest('div').show();

                if (tipoActual == '0') {
                    // Agrícola
                    $('#col1').text('Cultivo');
                    $('#col2').text('Maleza');
                    $('.dosis-col').show();
                } else {
                    // Veterinario
                    $('#col1').text('Especie');
                    $('#col2').text('Subespecie');
                    $('.dosis-col').hide();
                }

                $('#tablaDosificacion tbody').empty(); // Limpiar filas anteriores
            });

            // Agregar ingrediente activo
            $('#btnAgregarIngrediente').click(function() {
                const row = `
                <tr>
                    <td>
                        <select class="form-select select-ingrediente" style="width: 100%"></select>
                    </td>
                    <td>
    <input type="number" class="form-control" placeholder="Composición" step="0.01" />
</td>

                    <td>
                        <select class="form-select select-unidad" style="width: 100%"></select>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm btn-remove">Eliminar</button></td>
                </tr>
            `;
                $('#tablaIngredientes tbody').append(row);
                cargarSelect('/ingredientes-activos', '.select-ingrediente:last',
                    'ingrediente_activo_nombre');
                cargarSelect('/unidad-medida', '.select-unidad:last', 'unidad_medida_detalle');
            });

            // Agregar dosificación
            $('#btnAgregarDosis').click(function() {
                let row;

                if (tipoActual === '0') {
                    // Agrícola
                    row = `
<tr>
    <td><select class="form-select select-cultivo" style="width: 100%"></select></td>
    <td><select class="form-select select-maleza" style="width: 100%"></select></td>
    <td class="dosis-col"><input class="form-control" placeholder="Dosis" /></td>
    <td class="dosis-col">
        <select class="form-select select-unidad-dosificacion" style="width: 100%"></select>
    </td>
    <td><input class="form-control" placeholder="Aplicación" /></td>
    <td><button type="button" class="btn btn-danger btn-sm btn-remove">Eliminar</button></td>
</tr>
`;

                } else {
                    // Veterinario
                    row = `
                <tr>
                    <td><select class="form-select select-especie" style="width: 100%"></select></td>
                    <td><select class="form-select select-subespecie" style="width: 100%"></select></td>
                    <td class="dosis-col d-none"></td>
                    <td class="dosis-col d-none"></td>
                    <td><input class="form-control" placeholder="Aplicación" /></td>
                    <td><button type="button" class="btn btn-danger btn-sm btn-remove">Eliminar</button></td>
                </tr>
            `;
                }

                $('#tablaDosificacion tbody').append(row);

                // Cargar opciones en selects
                if (tipoActual === '0') {
                    cargarSelect('/cultivos', '.select-cultivo:last', 'cultivo_nombre');
                    cargarSelect('/maleza', '.select-maleza:last', 'maleza_nombre');
                    cargarSelect('/unidad-medida-dosificacion', '.select-unidad-dosificacion:last',
                        'unidad_medida_dosificacion_representacion');
                    3

                } else {
                    cargarSelect('/especie', '.select-especie:last', 'especie_nombre');

                    // Cargar subespecies al seleccionar especie
                    $('.select-especie:last').on('change', function() {
                        const especieId = $(this).val();
                        const $sub = $(this).closest('tr').find('.select-subespecie');
                        $sub.empty();
                        $.get(`/subespecie/especie/${especieId}`, function(data) {
                            data.forEach(item => {
                                $sub.append(new Option(item.subespecie_nombre, item
                                    .subespecie_id));
                            });
                        });
                    });
                }
            });

            // Eliminar filas dinámicas
            $(document).on('click', '.btn-remove', function() {
                $(this).closest('tr').remove();
            });

            Promise.all([
                new Promise(resolve => cargarSelect('/unidad-medida', '#selectUnidad',
                    'unidad_medida_detalle', resolve)),
                new Promise(resolve => cargarSelect('/formulacion', '#selectFormulacion', item =>
                    `${item.formulacion_abreviatura} - ${item.formulacion_nombre}`, resolve))
            ]).then(() => {
                $('#formProducto').fadeIn(); // Muestra el formulario ya cargado
                $('#loadingModal').modal('hide');
            });

            function cargarSelect(url, selector, labelField = null, callback = null) {
                $.get(url, function(data) {
                    const $select = $(selector);
                    $select.empty().append(new Option('Seleccione', ''));

                    data.forEach(item => {
                        let label = typeof labelField === 'function' ? labelField(item) : item[
                            labelField];

                        // Corregir búsqueda del id
                        const idKey = Object.keys(item).find(k => k.endsWith('_id'));
                        const id = item.id || (typeof labelField === 'string' && item[
                            `${labelField.split('_')[0]}_id`]) || (idKey ? item[idKey] : null);

                        $select.append(new Option(label, id));
                    });

                    if (typeof callback === 'function') callback();
                });
            }

            // Enviar formulario con ingredientes y dosificación
            $('#formProducto').on('submit', function(e) {
                e.preventDefault();
                $('#loadingModal').modal('show');

                // Armar el objeto de datos principal
                const data = {
                    tipo_producto: $('#tipoProducto').val(),
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

                // Ingredientes activos
                $('#tablaIngredientes tbody tr').each(function() {
                    const ingrediente_id = $(this).find('.select-ingrediente').val();
                    const porcentaje = $(this).find('input[type="number"]').val();
                    const unidad_id = $(this).find('.select-unidad').val();

                    if (ingrediente_id && porcentaje && unidad_id) {
                        data.ingredientes.push({
                            ingrediente_id: ingrediente_id,
                            porcentaje: porcentaje,
                            unidad_id: unidad_id
                        });
                    }
                });

                // Dosificaciones
                $('#tablaDosificacion tbody tr').each(function() {
                    const tipo = $('#tipoProducto').val();
                    let item = {
                        aplicacion: $(this).find('input[placeholder="Aplicación"]').val()
                    };

                    if (tipo === '0') {
                        // Agrícola
                        item.cultivo_id = $(this).find('.select-cultivo').val();
                        item.maleza_id = $(this).find('.select-maleza').val();
                        item.dosis = $(this).find('input[placeholder="Dosis"]').val();
                        item.unidad_dosificacion_id = $(this).find('select').eq(2)
                            .val(); // 3er select
                    } else {
                        // Veterinario
                        item.subespecie_id = $(this).find('.select-subespecie').val();
                    }

                    data.dosificaciones.push(item);
                });

                // Enviar al servidor
                $.ajax({
                    url: '/producto', // o la ruta correcta para crear
                    method: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        //alert('Producto registrado correctamente');
                        $('#loadingModal').modal('hide');
                        window.location.href = routeToIndex;
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Error al registrar el producto');
                    }
                });
            });

        });
    </script>
@endsection
