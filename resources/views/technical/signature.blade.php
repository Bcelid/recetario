@extends('layouts.app')

@section('title', 'Firmas Técnicos')

@section('content')
    <h2>Gestión de Firmas</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all" selected>Todos</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewFirma">Nueva Firma</button>
    </div>

    <table id="firmasTable" class="display table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Técnico</th>
                <th>Nombre</th>
                <th>Firma</th>
                <th>Fecha Emisión</th>
                <th>Fecha Expiración</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Modal Crear/Editar Firma -->
    <div class="modal fade" id="firmaModal" tabindex="-1" aria-labelledby="firmaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="firmaForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="firmaModalLabel">Nueva Firma</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="tecnico_firma_id" name="tecnico_firma_id">
                        <div class="mb-3">
                            <label for="tecnico_id" class="form-label">Técnico *</label>
                            <select class="form-select" name="tecnico_id" id="tecnico_id" required>
                                <!-- Se carga por Ajax -->
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="tecnico_firma_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="tecnico_firma_nombre" id="tecnico_firma_nombre"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="tecnico_firma_ruta" class="form-label">Archivo Firma *</label>
                            <input type="file" class="form-control" name="tecnico_firma_ruta" id="tecnico_firma_ruta"">
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Formatos permitido: .p12 .pum</small>
                        </div>

                        <div class="mb-3">
                            <label for="tecnico_firma_clave" class="form-label">Clave *</label>
                            <input type="password" class="form-control" name="tecnico_firma_clave" id="tecnico_firma_clave"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_emision" class="form-label">Fecha Emisión *</label>
                            <input type="date" class="form-control" name="fecha_emision" id="fecha_emision" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_expiracion" class="form-label">Fecha Expiración *</label>
                            <input type="date" class="form-control" name="fecha_expiracion" id="fecha_expiracion"
                                required>
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
            let firmaModal = new bootstrap.Modal(document.getElementById('firmaModal'));
            let isEdit = false;

            // Cargar técnicos para el select
            function loadTecnicos() {
                $.get('/tecnico?estado=1', function(tecnicos) {
                    $('#tecnico_id').empty();
                    tecnicos.forEach(t => {
                        $('#tecnico_id').append(
                            `<option value="${t.tecnico_id}">${t.tecnico_nombre} ${t.tecnico_apellido}</option>`
                        );
                    });
                });
            }

            // Inicializar DataTable
            let table = $('#firmasTable').DataTable({
                ajax: {
                    url: '/tecnico-firma',
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val();
                    }
                },
                columns: [{
                        data: 'tecnico_firma_id'
                    },
                    {
                        data: 'tecnico',
                        render: function(tecnico) {
                            if (!tecnico) return '';
                            return `${tecnico.tecnico_nombre} ${tecnico.tecnico_apellido}`;
                        },
                        defaultContent: ''
                    },
                    {
                        data: 'tecnico_firma_nombre'
                    },
                    {
                        data: 'tecnico_firma_ruta',
                        render: d => d ? `<a href="/storage/${d}" target="_blank">Descargar</a>` : ''
                    },
                    {
                        data: 'fecha_emision'
                    },
                    {
                        data: 'fecha_expiracion'
                    },

                    {
                        data: 'tecnico_firma_estado',
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
                            <button class="btn btn-sm btn-primary btn-edit" data-id="${data.tecnico_firma_id}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm ${data.tecnico_firma_estado == 1 ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.tecnico_firma_id}">
                                ${data.tecnico_firma_estado == 1 ? '<i class="bi bi-x-circle"></i>' : '<i class="bi bi-check-circle"></i>'}
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

            // Nueva firma
            $('#btnNewFirma').click(function() {
                isEdit = false;
                $('#firmaForm')[0].reset();
                $('#firmaForm').find('.is-invalid').removeClass('is-invalid');
                $('#firmaForm').find('.invalid-feedback').text('');
                $('#tecnico_firma_id').val('');
                $('#firmaModalLabel').text('Nueva Firma');
                loadTecnicos();
                firmaModal.show();
            });

            // Editar firma
            $('#firmasTable').on('click', '.btn-edit', function() {
                isEdit = true;
                let id = $(this).data('id');
                $('#firmaForm').find('.is-invalid').removeClass('is-invalid');
                $('#firmaForm').find('.invalid-feedback').text('');
                $('#firmaModalLabel').text('Editar Firma');

                $.get(`/tecnico-firma/${id}`, function(data) {
                    $('#tecnico_firma_id').val(data.tecnico_firma_id);
                    $('#tecnico_firma_nombre').val(data.tecnico_firma_nombre);
                    $('#tecnico_firma_clave').val(data.tecnico_firma_clave);
                    $('#fecha_emision').val(data.fecha_emision);
                    $('#fecha_expiracion').val(data.fecha_expiracion);

                    loadTecnicos();
                    setTimeout(() => $('#tecnico_id').val(data.tecnico_id), 200);

                    firmaModal.show();
                });
            });

            // Guardar firma
            $('#firmaForm').submit(function(e) {
                e.preventDefault();

                let id = $('#tecnico_firma_id').val();
                let url = id ? `/tecnico-firma/${id}` : '/tecnico-firma';
                let formData = new FormData(this);

                if (id) {
                    formData.append('_method', 'PUT');
                }
 
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        firmaModal.hide();
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
            $('#firmasTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado de la firma?')) return;

                let id = $(this).data('id');

                $.ajax({
                    url: `/tecnico-firma/${id}`,
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
