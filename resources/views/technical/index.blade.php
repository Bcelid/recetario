@extends('layouts.app')

@section('title', 'Técnicos')

@section('content')

    <h2>Gestión de Técnicos</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all">Todos</option>
            <option value="1" selected>Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewTecnico">Nuevo Técnico</button>
    </div>
    <div class="table-responsive">
        <table id="tecnicosTable" class="display table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Modal Crear/Editar Técnico -->
    <div class="modal fade" id="tecnicoModal" tabindex="-1" aria-labelledby="tecnicoModalLabel" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog">
            <form id="tecnicoForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tecnicoModalLabel">Nuevo Técnico</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="tecnicoId" name="tecnicoId">

                        <div class="mb-3">
                            <label for="tecnido_cedula" class="form-label">Cédula *</label>
                            <input type="number" class="form-control" name="tecnido_cedula" id="tecnido_cedula" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="tecnico_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="tecnico_nombre" id="tecnico_nombre" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="tecnico_apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" name="tecnico_apellido" id="tecnico_apellido"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="tecnico_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" name="tecnico_email" id="tecnico_email" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="tecnico_telefono" class="form-label">Teléfono</label>
                            <input type="number" class="form-control" name="tecnico_telefono" id="tecnico_telefono">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Categoría *</label>
                            <select class="form-select" name="categoria_id" id="categoria_id" required>
                                <!-- Cargado por Ajax -->
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="tecnico_senescyt" class="form-label">Registro Senescyt</label>
                            <input type="text" class="form-control" name="tecnico_senescyt" id="tecnico_senescyt">
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarTecnico">
                            Guardar
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let tecnicoModal = new bootstrap.Modal(document.getElementById('tecnicoModal'));
            let isEdit = false;

            // Cargar categorías para el select
            function loadCategorias() {
                $.get('/tecnico-categorias?estado=1', function(categorias) {
                    $('#categoria_id').empty();
                    categorias.forEach(c => {
                        $('#categoria_id').append(
                            `<option value="${c.tecnico_categoria_id}">${c.tecnico_categoria_nombre}</option>`
                        );
                    });
                });
            }

            // Inicializar DataTable
            let table = $('#tecnicosTable').DataTable({
                //language: {
                //    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                //},
                ajax: {
                    url: 'tecnico',
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val();
                    }
                },
                columns: [{
                        data: 'tecnico_id'
                    },
                    {
                        data: 'tecnido_cedula'
                    },
                    {
                        data: null,
                        render: d => `${d.tecnico_nombre} ${d.tecnico_apellido}`
                    },
                    {
                        data: 'tecnico_email'
                    },
                    {
                        data: 'tecnico_telefono'
                    },
                    {
                        data: 'categoria.tecnico_categoria_nombre'
                    },
                    {
                        data: 'tecnico_estado',
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
                            <button class="btn btn-sm btn-primary btn-edit" data-id="${data.tecnico_id}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-sm ${data.tecnico_estado == 1 ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.tecnico_id}">
                                ${data.tecnico_estado == 1 ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
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

            // Abrir modal para nuevo técnico
            $('#btnNewTecnico').click(function() {
                isEdit = false;
                $('#tecnicoForm')[0].reset();
                $('#tecnicoForm').find('.is-invalid').removeClass('is-invalid');
                $('#tecnicoForm').find('.invalid-feedback').text('');
                $('#tecnicoId').val('');
                $('#tecnicoModalLabel').text('Nuevo Técnico');
                loadCategorias();
                tecnicoModal.show();
            });

            // Editar técnico
            $('#tecnicosTable').on('click', '.btn-edit', function() {
                isEdit = true;
                let id = $(this).data('id');
                $('#tecnicoForm').find('.is-invalid').removeClass('is-invalid');
                $('#tecnicoForm').find('.invalid-feedback').text('');
                $('#tecnicoModalLabel').text('Editar Técnico');

                $.get(`/tecnico/${id}`, function(data) {
                    $('#tecnicoId').val(data.tecnico_id);
                    $('#tecnido_cedula').val(data.tecnido_cedula);
                    $('#tecnico_nombre').val(data.tecnico_nombre);
                    $('#tecnico_apellido').val(data.tecnico_apellido);
                    $('#tecnico_email').val(data.tecnico_email);
                    $('#tecnico_telefono').val(data.tecnico_telefono);
                    $('#tecnico_senescyt').val(data.tecnico_senescyt);

                    loadCategorias();
                    setTimeout(() => $('#categoria_id').val(data.categoria_id), 200);

                    tecnicoModal.show();
                });
            });

            // Guardar técnico (crear o editar)
            $('#tecnicoForm').submit(function(e) {
                e.preventDefault();

                let $btn = $('#btnGuardarTecnico');
                let originalContent = $btn.html(); // Guardamos el contenido original del botón
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...'
                    );

                let id = $('#tecnicoId').val();
                let url = id ? `/tecnico/${id}` : '/tecnico';
                let method = id ? 'PUT' : 'POST';

                let data = {
                    tecnido_cedula: $('#tecnido_cedula').val(),
                    tecnico_nombre: $('#tecnico_nombre').val(),
                    tecnico_apellido: $('#tecnico_apellido').val(),
                    tecnico_email: $('#tecnico_email').val(),
                    tecnico_telefono: $('#tecnico_telefono').val(),
                    categoria_id: $('#categoria_id').val(),
                    tecnico_senescyt: $('#tecnico_senescyt').val(),
                };

                if (id) {
                    data._method = 'PUT';
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    success: function(res) {
                        $btn.html('<i class="fa fa-check text-white"></i> Guardado');
                        setTimeout(() => {
                            $('#tecnicoModal').modal('hide');
                            $btn.html(originalContent).prop('disabled', false);
                            $('#tecnicoForm')[0].reset();
                            $('#tecnicoForm').find('.is-invalid').removeClass(
                                'is-invalid');
                            $('#tecnicoForm').find('.invalid-feedback').text('');
                            table.ajax.reload(null, false);
                        }, 700); // Mostrar el ícono de check por 0.7s
                    },
                    error: function(xhr) {
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


            // Cambiar estado (activar/desactivar)
            $('#tecnicosTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado del técnico?')) return;

                let $btn = $(this);
                let id = $btn.data('id');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span>');


                $.ajax({
                    url: `/tecnico/${id}`,
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

        });
    </script>
@endsection
