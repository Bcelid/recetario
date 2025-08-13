@extends('layouts.app')

@section('title', 'Clientes')

@section('content')

    <h2>Gestión de Clientes</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all" selected>Todos</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewCliente">Nuevo Cliente</button>
    </div>

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

    <!-- Modal Crear/Editar Cliente -->
    <div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
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
                            <input type="text" class="form-control" name="cliente_apellido" id="cliente_apellido" required>
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
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
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

        let table = $('#clientesTable').DataTable({
            ajax: {
                url: '/cliente',
                dataSrc: '',
                data: function(d) {
                    d.estado = $('#filterEstado').val();
                }
            },
            columns: [
                { data: 'cliente_id' },
                { data: 'cliente_cedula' },
                {
                    data: null,
                    render: d => `${d.cliente_nombre} ${d.cliente_apellido}`
                },
                { data: 'cliente_direccion' },
                { data: 'almacen.almacen_nombre' },
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
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm ${data.cliente_estado == 1 ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.cliente_id}">
                                ${data.cliente_estado == 1 ? '<i class="bi bi-person-dash"></i>' : '<i class="bi bi-person-check"></i>'}
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

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function(res) {
                    clienteModal.hide();
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

        $('#clientesTable').on('click', '.btn-toggle-estado', function() {
            if (!confirm('¿Está seguro de cambiar el estado del cliente?')) return;

            let id = $(this).data('id');

            $.ajax({
                url: `/cliente/${id}`,
                method: 'DELETE',
                success: function(res) {
                    table.ajax.reload(null, false);
                    alert(res.message);
                },
                error: function() {
                    alert('Error al cambiar estado');
                }
            });
        });

    });
</script>
@endsection
