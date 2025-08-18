@extends('layouts.app')

@section('title', 'Almacenes')

@section('content')

    <h2>Gestión de Almacenes</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all">Todos</option>
            <option value="1" selected>Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewWarehouse">Nuevo Almacén</button>
    </div>
    <div class="table-responsive">
    <table id="warehouseTable" class="display table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Propietario</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    </div>

    <!-- Modal Crear/Editar Almacén -->
    <!-- Modal Crear/Editar Almacén -->
    <div class="modal fade" id="warehouseModal" tabindex="-1" aria-labelledby="warehouseModalLabel">
        <div class="modal-dialog">
            <form id="warehouseForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="warehouseModalLabel">Nuevo Almacén</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="warehouseId" name="warehouseId">

                        <div class="mb-3">
                            <label for="almacen_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="almacen_nombre" id="almacen_nombre" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="almacen_direccion" class="form-label">Dirección *</label>
                            <input type="text" class="form-control" name="almacen_direccion" id="almacen_direccion"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="almacen_telefono" class="form-label">Teléfono *</label>
                            <input type="text" class="form-control" name="almacen_telefono" id="almacen_telefono"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="almacen_correo" class="form-label">Correo electrónico *</label>
                            <input type="email" class="form-control" name="almacen_correo" id="almacen_correo" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="propietario_id" class="form-label">Propietario *</label>
                            <select class="form-select" name="propietario_id" id="propietario_id" style="width: 100%;"
                                required>
                                <option></option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="almacen_estado" class="form-label">Estado *</label>
                            <select class="form-select" name="almacen_estado" id="almacen_estado" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@section('scripts')
    <!-- Select2 para el buscador -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            let warehouseModal = new bootstrap.Modal(document.getElementById('warehouseModal'));
            let isEdit = false;

            // Inicializar Select2 en propietario
            $('#propietario_id').select2({
                dropdownParent: $('#warehouseModal'),
                placeholder: 'Seleccione un propietario',
                allowClear: true,
                ajax: {
                    url: '/almacen/search', // 
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // 
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    }
                }
            });

            // Inicializar DataTable
            let table = $('#warehouseTable').DataTable({
                ajax: {
                    url: '/almacen', // Ruta que devuelve JSON con almacenes
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val(); // filtro por estado si usas
                    }
                },
                columns: [{
                        data: 'almacen_id'
                    },
                    {
                        data: 'almacen_nombre'
                    },
                    {
                        data: 'almacen_direccion'
                    },
                    {
                        data: 'almacen_telefono'
                    },
                    {
                        data: 'almacen_correo'
                    },
                    {
                        data: 'propietario',
                        render: function(propietario) {
                            if (!propietario) return '<i>No asignado</i>';
                            return `${propietario.propietario_almacen_nombre} ${propietario.propietario_almacen_apellido}`;
                        }
                    },

                    {
                        data: 'almacen_estado',
                        render: function(d) {
                            return d == 1 ?
                                '<span class="badge bg-success">Activo</span>' :
                                '<span class="badge bg-secondary">Inactivo</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data) {
                            return `
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-primary btn-edit" data-id="${data.almacen_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-sm ${data.almacen_estado == 1 ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.almacen_id}">
                        ${data.almacen_estado == 1 ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
                    </button>
                </div>`;
                        }
                    }
                ]
            });


            // Filtro por estado
            $('#filterEstado').on('change', function() {
                table.ajax.reload();
            });

            // Abrir modal nuevo
            $('#btnNewWarehouse').click(function() {
                isEdit = false;
                $('#warehouseForm')[0].reset();
                $('#warehouseId').val('');
                $('#propietario_id').val(null).trigger('change');
                $('#warehouseModalLabel').text('Nuevo Almacén');
                warehouseModal.show();
            });

            // Guardar
            $('#warehouseForm').submit(function(e) {
                e.preventDefault();
                let id = $('#warehouseId').val();
                let url = id ? `/almacen/${id}` : '/almacen';
                let method = id ? 'PUT' : 'POST';

                let data = {
                    almacen_nombre: $('#almacen_nombre').val(),
                    almacen_direccion: $('#almacen_direccion').val(),
                    almacen_telefono: $('#almacen_telefono').val(),
                    almacen_correo: $('#almacen_correo').val(),
                    almacen_propietario_id: $('#propietario_id').val(), // aquí cambio el nombre
                    almacen_estado: $('#almacen_estado').val()
                };


                if (id) {
                    data._method = 'PUT';
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    success: function(res) {
                        warehouseModal.hide();
                        table.ajax.reload(null, false);
                        alert(res.message);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                let input = $(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.next('.invalid-feedback').text(errors[field][0]);
                            }
                        } else {
                            alert('Error en el servidor');
                        }
                    }
                });
            });

            // Cambiar estado
            $('#warehouseTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado del almacén?')) return;
                let id = $(this).data('id');

                $.ajax({
                    url: `/almacen/${id}`,
                    method: 'DELETE',
                    success: function(res) {
                        table.ajax.reload(null, false);
                        alert(res.message);
                    }
                });
            });

            // Editar almacén
            $('#warehouseTable').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                isEdit = true;

                // Limpia los errores anteriores
                $('#warehouseForm')[0].reset();
                $('#warehouseForm .form-control, #warehouseForm .form-select').removeClass('is-invalid');
                $('#warehouseForm .invalid-feedback').text('');

                $.get(`/almacen/${id}`, function(data) {
                    $('#warehouseId').val(data.almacen_id);
                    $('#almacen_nombre').val(data.almacen_nombre);
                    $('#almacen_direccion').val(data.almacen_direccion);
                    $('#almacen_telefono').val(data.almacen_telefono);
                    $('#almacen_correo').val(data.almacen_correo);
                    $('#almacen_estado').val(data.almacen_estado);

                    // Setear propietario en select2 (select2 requiere crear el option manualmente)
                    if (data.propietario) {
                        let option = new Option(
                            `${data.propietario.propietario_almacen_nombre} ${data.propietario.propietario_almacen_apellido}`,
                            data.propietario.propietario_almacen_id,
                            true,
                            true
                        );
                        $('#propietario_id').append(option).trigger('change');
                    } else {
                        $('#propietario_id').val(null).trigger('change');
                    }

                    $('#warehouseModalLabel').text('Editar Almacén');
                    warehouseModal.show();
                }).fail(function() {
                    alert('Error al cargar el almacén');
                });
            });

        });
    </script>
@endsection
