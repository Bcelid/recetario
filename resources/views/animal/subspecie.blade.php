@extends('layouts.app')

@section('title', 'Subespecies')

@section('content')

    <h2>Gestión de Subespecies</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all">Todos</option>
            <option value="1"selected>Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewSubespecie">Nueva Subespecie</button>
    </div>
    <div class="table-responsive">
        <table id="subespeciesTable" class="display table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Nombre Científico</th>
                    <th>Especie</th>
                    <th>Detalle</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <!-- Modal Crear/Editar Subespecie -->
    <div class="modal fade" id="subespecieModal" tabindex="-1" aria-labelledby="subespecieModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <form id="subespecieForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="subespecieModalLabel">Nueva Subespecie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="subespecieId" name="subespecieId">

                        <div class="mb-3">
                            <label for="subespecie_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="subespecie_nombre" name="subespecie_nombre"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="subespecie_cientifico" class="form-label">Nombre Científico *</label>
                            <input type="text" class="form-control" id="subespecie_cientifico"
                                name="subespecie_cientifico" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="especie_id" class="form-label">Especie *</label>
                            <select class="form-select" id="especie_id" name="especie_id" required>
                                <!-- Cargado por Ajax -->
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="subespecie_detalle" class="form-label">Detalle</label>
                            <textarea class="form-control" id="subespecie_detalle" name="subespecie_detalle" rows="3"></textarea>
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
            let subespecieModal = new bootstrap.Modal(document.getElementById('subespecieModal'));
            let isEdit = false;
            loadEspecies();

            function loadEspecies() {
                $.get('/subespecie/activos', function(data) {
                    $('#especie_id').empty();
                    data.forEach(especie => {
                        $('#especie_id').append(
                            `<option value="${especie.especie_id}">${especie.especie_nombre}</option>`
                        );
                    });
                });
            }

            let table = $('#subespeciesTable').DataTable({
                ajax: {
                    url: '/subespecie',
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val();
                    }
                },
                columns: [{
                        data: 'subespecie_id'
                    },
                    {
                        data: 'subespecie_nombre'
                    },
                    {
                        data: 'subespecie_cientifico'
                    },
                    {
                        data: 'especie.especie_nombre'
                    },
                    {
                        data: 'subespecie_detalle'
                    },
                    {
                        data: 'subespecie_estado',
                        render: function(data) {
                            return data == 1 ?
                                '<span class="badge bg-success">Activo</span>' :
                                '<span class="badge bg-secondary">Inactivo</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary btn-edit" data-id="${data.subespecie_id}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-sm ${data.subespecie_estado == 1 ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.subespecie_id}">
                                ${data.subespecie_estado == 1 ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
                            </button>
                        </div>`;
                        }
                    }
                ]
            });

            $('#filterEstado').on('change', function() {
                table.ajax.reload();
            });

            $('#btnNewSubespecie').click(function() {
                isEdit = false;
                $('#subespecieForm')[0].reset();
                $('#subespecieForm').find('.is-invalid').removeClass('is-invalid');
                $('#subespecieForm').find('.invalid-feedback').text('');
                $('#subespecieId').val('');
                $('#subespecieModalLabel').text('Nueva Subespecie');
                subespecieModal.show();
            });

            $('#subespeciesTable').on('click', '.btn-edit', function() {
                isEdit = true;
                let id = $(this).data('id');
                $('#subespecieForm').find('.is-invalid').removeClass('is-invalid');
                $('#subespecieForm').find('.invalid-feedback').text('');
                $('#subespecieModalLabel').text('Editar Subespecie');

                $.get(`/subespecie/${id}`, function(data) {
                    $('#subespecieId').val(data.subespecie_id);
                    $('#subespecie_nombre').val(data.subespecie_nombre);
                    $('#subespecie_cientifico').val(data.subespecie_cientifico);
                    $('#subespecie_detalle').val(data.subespecie_detalle);
                    setTimeout(() => $('#especie_id').val(data.especie_id), 200);

                    subespecieModal.show();
                });
            });

            $('#subespecieForm').submit(function(e) {
                e.preventDefault();

                let $btn = $('#subespecieForm button[type="submit"]');
                let original = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');

                $('#subespecieForm').find('.is-invalid').removeClass('is-invalid');
                $('#subespecieForm').find('.invalid-feedback').text('');

                let id = $('#subespecieId').val();
                let url = id ? `/subespecie/${id}` : '/subespecie';
                let method = id ? 'PUT' : 'POST';

                let data = {
                    subespecie_nombre: $('#subespecie_nombre').val(),
                    subespecie_cientifico: $('#subespecie_cientifico').val(),
                    subespecie_detalle: $('#subespecie_detalle').val(),
                    especie_id: $('#especie_id').val(),
                };

                if (id) {
                    data._method = 'PUT';
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    success: function(res) {
                        $btn.removeClass('btn-primary').addClass('btn-success')
                            .html('<i class="fa fa-check text-white"></i> Guardado');

                        setTimeout(() => {
                            subespecieModal.hide();
                            table.ajax.reload(null, false);
                            $('#subespecieForm')[0].reset();
                            $btn.removeClass('btn-success').addClass('btn-primary')
                                .html(original)
                                .prop('disabled', false);
                        }, 1000);
                    },
                    error: function(xhr) {
                        $btn.html(original).prop('disabled', false);

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


            $('#subespeciesTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado de la subespecie?')) return;

                let $btn = $(this);
                let id = $btn.data('id');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span>');


                $.ajax({
                    url: `/subespecie/${id}`,
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
