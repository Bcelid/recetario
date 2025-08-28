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
                    <th class="text-center">Logo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Modal Crear/Editar Almacén -->
    <div class="modal fade" id="warehouseModal" tabindex="-1" aria-labelledby="warehouseModalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <form id="warehouseForm" enctype="multipart/form-data">
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
                            <select class="form-select" name="almacen_propietario_id" id="propietario_id"
                                style="width: 100%;" required>
                                <option></option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="almacen_logo" class="form-label">Logo</label>
                            <input type="file" class="form-control" name="almacen_logo" id="almacen_logo"
                                accept="image/*">
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
                        <button type="submit" class="btn btn-primary" id="btnSaveWarehouse">Guardar</button>

                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            let warehouseModal = new bootstrap.Modal(document.getElementById('warehouseModal'));
            let table = $('#warehouseTable').DataTable({
                ajax: {
                    url: '/almacen',
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val();
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
                        render: p => p ?
                            `${p.propietario_almacen_nombre} ${p.propietario_almacen_apellido}` :
                            '<i>No asignado</i>'
                    },
                    {
                        data: 'almacen_logo',
                        render: logo => logo ?
                            `<img src="/storage/${logo}?t=${new Date().getTime()}" alt="Logo" class="img-fluid" style="height:40px;">` :
                            '<i>Sin logo</i>'
                    },

                    {
                        data: 'almacen_estado',
                        render: d => d == 1 ? '<span class="badge bg-success">Activo</span>' :
                            '<span class="badge bg-secondary">Inactivo</span>'
                    },
                    {
                        data: null,
                        orderable: false,
                        render: data => `
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-primary btn-edit" data-id="${data.almacen_id}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-sm ${data.almacen_estado == 1 ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.almacen_id}">
                                    ${data.almacen_estado == 1 ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
                                </button>
                            </div>`
                    }
                ],
                columnDefs: [{
                        targets: 6,
                        className: 'text-center'
                    } // Columna del logo (índice 6)
                ]
            });

            $('#filterEstado').change(() => table.ajax.reload());

            $('#btnNewWarehouse').click(() => {
                $('#warehouseForm')[0].reset();
                $('#warehouseId').val('');
                $('#propietario_id').val(null).trigger('change');
                warehouseModal.show();
                $('#warehouseModalLabel').text('Nuevo Almacén');
            });

            $('#propietario_id').select2({
                dropdownParent: $('#warehouseModal'),
                placeholder: 'Seleccione un propietario',
                allowClear: true,
                ajax: {
                    url: '/almacen/search',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: data.results
                    })
                }
            });

            $('#warehouseForm').submit(function(e) {
                e.preventDefault();

                let $btn = $('#btnSaveWarehouse');
                let originalContent = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');

                let id = $('#warehouseId').val();
                let url = id ? `/almacen/${id}` : '/almacen';
                let formData = new FormData(this);
                if (id) formData.append('_method', 'PUT');

                $('#warehouseForm .is-invalid').removeClass('is-invalid');
                $('#warehouseForm .invalid-feedback').text('');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: res => {
                        $btn.html('<i class="fa fa-check text-white"></i> Guardado');

                        setTimeout(() => {
                            warehouseModal.hide();
                            table.ajax.reload(null, false);
                            $btn.html(originalContent).prop('disabled', false);
                            $('#warehouseForm')[0].reset();
                            $('#propietario_id').val(null).trigger('change');
                        }, 1000);
                    },
                    error: xhr => {
                        $btn.html(originalContent).prop('disabled', false);

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


            $('#warehouseTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado del almacén?')) return;
                let $btn = $(this);
                let id = $btn.data('id');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span>');

                $.ajax({
                    url: `/almacen/${id}`,
                    method: 'DELETE',
                    success: res => {
                        table.ajax.reload(null, false);
                        //alert(res.message);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            $('#warehouseTable').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                $('#warehouseForm')[0].reset();
                $('#warehouseForm .is-invalid').removeClass('is-invalid');
                $('#warehouseForm .invalid-feedback').text('');

                $.get(`/almacen/${id}`, data => {
                    $('#warehouseId').val(data.almacen_id);
                    $('#almacen_nombre').val(data.almacen_nombre);
                    $('#almacen_direccion').val(data.almacen_direccion);
                    $('#almacen_telefono').val(data.almacen_telefono);
                    $('#almacen_correo').val(data.almacen_correo);
                    $('#almacen_estado').val(data.almacen_estado);

                    $('#propietario_id').empty().append(new Option(
                        `${data.propietario.propietario_almacen_nombre} ${data.propietario.propietario_almacen_apellido}`,
                        data.propietario.propietario_almacen_id,
                        true,
                        true
                    )).trigger('change');

                    $('#warehouseModalLabel').text('Editar Almacén');
                    warehouseModal.show();
                }).fail(() => alert('Error al cargar el almacén'));
            });
        });
    </script>
@endsection
