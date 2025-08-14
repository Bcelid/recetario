@extends('layouts.app')

@section('title', 'Gestión de Especies')

@section('content')

    <h2>Gestión de Especies</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all" selected>Todos</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewEspecie">Nueva Especie</button>
    </div>

    <table id="especiesTable" class="display table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Nombre Científico</th>
                <th>Detalle</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            {{-- Se llenará por Ajax --}}
        </tbody>
    </table>

    <!-- Modal Crear/Editar Especie -->
    <div class="modal fade" id="especieModal" tabindex="-1" aria-labelledby="especieModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="especieForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="especieModalLabel">Nueva Especie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="especieId" name="especieId">

                        <div class="mb-3">
                            <label for="nombre_especie" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre_especie" name="especie_nombre" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_cientifico" class="form-label">Nombre Científico *</label>
                            <input type="text" class="form-control" id="nombre_cientifico" name="especie_cientifico"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="detalle" class="form-label">Detalle</label>
                            <textarea class="form-control" id="detalle" name="especie_detalle" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveEspecie">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let especieModal = new bootstrap.Modal(document.getElementById('especieModal'));
            let isEdit = false;

            let table = $('#especiesTable').DataTable({
                ajax: {
                    url: '/especie',
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val();
                    }
                },
                columns: [{
                        data: 'especie_id'
                    },
                    {
                        data: 'especie_nombre'
                    },
                    {
                        data: 'especie_cientifico'
                    },
                    {
                        data: 'especie_detalle'
                    },
                    {
                        data: 'especie_estado',
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
                        render: function(data, type, row) {
                            const btnEdit = `
                                <button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.especie_id}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            `;
                            const btnToggleEstado = `
                                <button class="btn btn-sm ${row.especie_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" 
                                    title="${row.especie_estado ? 'Desactivar' : 'Activar'}" 
                                    data-id="${row.especie_id}">
                                    ${row.especie_estado 
                                        ? '<i class="fa-solid fa-xmark-circle"></i>' 
                                        : '<i class="fa-solid fa-check-circle"></i>'}
                                </button>
                            `;
                            return `<div class="d-flex gap-1">${btnEdit}${btnToggleEstado}</div>`;
                        }

                    }
                ]
            });

            $('#filterEstado').on('change', function() {
                table.ajax.reload();
            });

            $('#btnNewEspecie').click(function() {
                isEdit = false;
                $('#especieModalLabel').text('Nueva Especie');
                $('#especieForm')[0].reset();
                $('#especieId').val('');
                clearValidationErrors();
                especieModal.show();
            });

            $('#especiesTable').on('click', '.btn-edit', function() {
                isEdit = true;
                clearValidationErrors();
                let id = $(this).data('id');

                $('#especieModalLabel').text('Editar Especie');
                $('#especieForm')[0].reset();
                $('#especieId').val(id);

                $.get(`/especie/${id}`, function(data) {
                    $('#nombre_especie').val(data.especie_nombre);
                    $('#nombre_cientifico').val(data.especie_cientifico);
                    $('#detalle').val(data.especie_detalle);
                    especieModal.show();
                });
            });

            function clearValidationErrors() {
                $('#especieForm').find('.is-invalid').removeClass('is-invalid');
                $('#especieForm').find('.invalid-feedback').text('');
            }

            $('#especieForm').submit(function(e) {
                e.preventDefault();
                clearValidationErrors();

                let id = $('#especieId').val();
                let url = isEdit ? `/especie/${id}` : '/especie';
                let method = isEdit ? 'PATCH' : 'POST';

                let formData = {
                    especie_nombre: $('#nombre_especie').val(),
                    especie_cientifico: $('#nombre_cientifico').val(),
                    especie_detalle: $('#detalle').val(),
                };

                if (method === 'PATCH') {
                    formData._method = 'PATCH';
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(res) {
                        especieModal.hide();
                        table.ajax.reload(null, false);
                        alert(res.message);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                let input = $(`[name=${field}]`);
                                input.addClass('is-invalid');
                                input.next('.invalid-feedback').text(errors[field][0]);
                            }
                        } else {
                            alert('Error en el servidor');
                        }
                    }
                });
            });

            $('#especiesTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado de la especie?')) return;

                let id = $(this).data('id');

                $.ajax({
                    url: `/especie/${id}`,
                    method: 'DELETE',
                    success: function(res) {
                        alert(res.message);
                        table.ajax.reload(null, false);
                    },
                    error: function() {
                        alert('Error al cambiar estado de la especie');
                    }
                });
            });
        });
    </script>
@endsection
