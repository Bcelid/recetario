@extends('layouts.app')

@section('title', 'Clientes')

@section('content')

    <h2>Gestión de Clientes</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all">Todos</option>
            <option value="1" selected>Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewCliente">Nuevo Cliente</button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importarClientesModal">
            Importar Clientes
        </button>
    </div>
    <div class="table-responsive">
        <table id="clientesTable" class="display table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Almacén</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <!-- Modal Crear/Editar Cliente -->
    <div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <form id="clienteForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clienteModalLabel">Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="clienteId" name="clienteId">

                        <div class="mb-3">
                            <label for="cliente_cedula" class="form-label">Cédula *</label>
                            <input type="text" class="form-control" name="cliente_cedula" id="cliente_cedula" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="cliente_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="cliente_nombre" id="cliente_nombre" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="cliente_apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" name="cliente_apellido" id="cliente_apellido"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="cliente_direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="cliente_direccion" id="cliente_direccion">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="cliente_almacen_id" class="form-label">Almacén *</label>
                            <select class="form-select" name="cliente_almacen_id" id="cliente_almacen_id" required>
                                <!-- Se carga por AJAX -->
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveCliente">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="importarClientesModal" tabindex="-1" aria-labelledby="importarClientesLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Encabezado del Modal -->
                <div class="modal-header">
                    <h5 class="modal-title" id="importarClientesLabel">Importar Clientes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="modal-body">
                    <form id="importarClientesForm">

                        <!-- Selección de Almacén -->
                        <div class="mb-3">
                            <label for="cliente_almacen_id" class="form-label">Almacén *</label>
                            <select class="form-select" name="almacen_id" id="cliente_almacen_id_import" required>

                                <!-- Se carga por AJAX -->
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Subida de Archivo Excel -->
                        <div class="mb-3">
                            <label for="archivoExcel" class="form-label">Subir archivo Excel</label>
                            <input class="form-control" type="file" id="archivoExcel" name="excel_file"
                                accept=".xlsx,.xls" required>

                        </div>

                    </form>
                </div>

                <!-- Footer del Modal -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"  id="btnImportarClientes" form="importarClientesForm">Importar</button>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let clienteModal = new bootstrap.Modal(document.getElementById('clienteModal'));
            let isEdit = false;

            function loadAlmacenes() {
                $.get('/almacen?estado=1', function(almacenes) {
                    $('#cliente_almacen_id').empty();
                    almacenes.forEach(a => {
                        $('#cliente_almacen_id').append(
                            `<option value="${a.almacen_id}">${a.almacen_nombre}</option>`
                        );
                    });
                });
            }
            // Para el modal de importar
            $('#importarClientesModal').on('shown.bs.modal', function() {
                $('#archivoExcel').val('');
                $.get('/almacen?estado=1', function(almacenes) {
                    const $select = $('#cliente_almacen_id_import');
                    $select.empty();
                    almacenes.forEach(a => {
                        $select.append(
                            `<option value="${a.almacen_id}">${a.almacen_nombre}</option>`
                        );
                    });
                });
            });

            let table = $('#clientesTable').DataTable({
                ajax: {
                    url: '/cliente',
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val();
                    }
                },
                columns: [{
                        data: 'cliente_id'
                    },
                    {
                        data: 'cliente_cedula'
                    },
                    {
                        data: null,
                        render: d => `${d.cliente_nombre} ${d.cliente_apellido}`
                    },
                    {
                        data: 'cliente_direccion'
                    },
                    {
                        data: 'almacen.almacen_nombre'
                    },
                    {
                        data: 'cliente_estado',
                        render: d => d == 1 ?
                            '<span class="badge bg-success">Activo</span>' :
                            '<span class="badge bg-secondary">Inactivo</span>'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary btn-edit" data-id="${data.cliente_id}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-sm ${data.cliente_estado == 1 ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.cliente_id}">
                                ${data.cliente_estado == 1 ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
                            </button>
                        </div>`;
                        }
                    }
                ]
            });

            $('#filterEstado').on('change', function() {
                table.ajax.reload();
            });

            $('#btnNewCliente').click(function() {
                isEdit = false;
                $('#clienteForm')[0].reset();
                $('#clienteForm').find('.is-invalid').removeClass('is-invalid');
                $('#clienteForm').find('.invalid-feedback').text('');
                $('#clienteId').val('');
                $('#clienteModalLabel').text('Nuevo Cliente');
                loadAlmacenes();
                clienteModal.show();
            });

            $('#clientesTable').on('click', '.btn-edit', function() {
                isEdit = true;
                let id = $(this).data('id');
                $('#clienteForm').find('.is-invalid').removeClass('is-invalid');
                $('#clienteForm').find('.invalid-feedback').text('');
                $('#clienteModalLabel').text('Editar Cliente');

                $.get(`/cliente/${id}`, function(data) {
                    $('#clienteId').val(data.cliente_id);
                    $('#cliente_cedula').val(data.cliente_cedula);
                    $('#cliente_nombre').val(data.cliente_nombre);
                    $('#cliente_apellido').val(data.cliente_apellido);
                    $('#cliente_direccion').val(data.cliente_direccion);
                    loadAlmacenes();
                    setTimeout(() => $('#cliente_almacen_id').val(data.cliente_almacen_id), 200);

                    clienteModal.show();
                });
            });

            $('#clienteForm').submit(function(e) {
                e.preventDefault();

                let $btn = $('#btnSaveCliente');
                let original = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');

                let id = $('#clienteId').val();
                let url = id ? `/cliente/${id}` : '/cliente';
                let method = id ? 'PUT' : 'POST';

                let data = {
                    cliente_cedula: $('#cliente_cedula').val(),
                    cliente_nombre: $('#cliente_nombre').val(),
                    cliente_apellido: $('#cliente_apellido').val(),
                    cliente_direccion: $('#cliente_direccion').val(),
                    cliente_almacen_id: $('#cliente_almacen_id').val(),
                };

                if (id) {
                    data._method = 'PUT';
                }

                $('#clienteForm .is-invalid').removeClass('is-invalid');
                $('#clienteForm .invalid-feedback').text('');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    success: function(res) {
                        $btn.html('<i class="fa fa-check text-white"></i> Guardado');
                        setTimeout(() => {
                            clienteModal.hide();
                            table.ajax.reload(null, false);
                            $('#clienteForm')[0].reset();
                            $btn.html(original).prop('disabled', false);
                        }, 800);
                    },
                    error: function(xhr) {
                        // Restaurar el botón original y habilitarlo
                        $btn.html(original).prop('disabled', false);

                        // Comprobar si el error es 422 o 400
                        if (xhr.status === 422 || xhr.status === 400) {
                            let errors = xhr.responseJSON.errors;

                            // Recorremos los errores y los mostramos en los campos correspondientes
                            for (let field in errors) {
                                let input = $(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.next('.invalid-feedback').text(errors[field][0]);
                            }

                            // Si existe un mensaje personalizado en la respuesta JSON, lo mostramos
                            if (xhr.responseJSON.message) {
                                alert(xhr.responseJSON
                                    .message); // O mostrarlo en un contenedor de notificación
                            }
                        } else {
                            // Mostrar un mensaje genérico si ocurre un error distinto
                            alert('Error en el servidor');
                        }
                    }
                });
            });


            $('#clientesTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado del cliente?')) return;

                let $btn = $(this);
                let id = $btn.data('id');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span>');


                $.ajax({
                    url: `/cliente/${id}`,
                    method: 'DELETE',
                    success: function(res) {
                        table.ajax.reload(null, false);
                        //alert(res.message);
                    },
                    error: function() {
                        alert('Error al cambiar estado');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });


            $('#importarClientesForm').submit(function(e) {
                e.preventDefault();

                let $btn = $('#btnImportarClientes');
                let originalHtml = $btn.html();

                // Verifica que el texto original y el cambio al spinner se están aplicando correctamente.
                console.log('Original Button Text:', originalHtml);
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Subiendo...');

                // Asegúrate de que el spinner se ve correctamente
                console.log('Button Text After Change:', $btn.html());


                let formData = new FormData(this);

                $.ajax({
                    url: '/client/import',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        alert(res.message + '\nClientes duplicados:\n' + res.clientes_duplicados
                            .join('\n'));
                        $('#importarClientesModal').modal('hide');
                        table.ajax.reload(); // Recarga la tabla de clientes
                    },
                    error: function(xhr) {
                        var errorMsg = 'Error al importar los clientes';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg =
                                `Error: ${xhr.responseJSON.error}\nArchivo: ${xhr.responseJSON.file}\nLínea: ${xhr.responseJSON.line}`;
                        } else {
                            errorMsg =
                                'Hubo un error al procesar la solicitud. Por favor, intenta nuevamente.';
                        }
                        alert(errorMsg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });


        });
    </script>
@endsection
