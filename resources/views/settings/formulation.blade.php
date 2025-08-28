@extends('layouts.app')

@section('title', 'Formulaciones')

@section('content')

    <h2>Formulaciones</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all">Todos</option>
            <option value="1"selected>Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewFormulacion">Nueva Formulación</button>
    </div>
    <div class="table-responsive">
        <table id="formulacionesTable" class="display table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Abreviatura</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {{-- Se llenará por Ajax --}}
            </tbody>
        </table>
    </div>
    <!-- Modal Crear/Editar Formulación -->
    <div class="modal fade" id="formulacionModal" tabindex="-1" aria-labelledby="formulacionModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <form id="formulacionForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formulacionModalLabel">Nueva Formulación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="formulacionId" name="formulacionId">

                        <div class="mb-3">
                            <label for="formulacion_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="formulacion_nombre" name="formulacion_nombre"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="formulacion_abreviatura" class="form-label">Abreviatura</label>
                            <input type="text" class="form-control" id="formulacion_abreviatura"
                                name="formulacion_abreviatura">
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveFormulacion">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let formulacionModal = new bootstrap.Modal(document.getElementById('formulacionModal'));
            let isEdit = false;

            // Inicializar DataTable
            let table = $('#formulacionesTable').DataTable({
                ajax: {
                    url: '/formulacion',
                    dataSrc: '',
                    data: function(d) {
                        let estado = $('#filterEstado').val();
                        d.estado = estado !== 'all' ? estado : '';
                    }
                },
                columns: [{
                        data: 'formulacion_id'
                    },
                    {
                        data: 'formulacion_nombre'
                    },
                    {
                        data: 'formulacion_abreviatura'
                    },
                    {
                        data: 'formulacion_estado',
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
                        render: function(row) {
                            const btnEdit = `<button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.formulacion_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`;

                            const btnToggleEstado = `<button class="btn btn-sm ${row.formulacion_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" title="${row.formulacion_estado ? 'Desactivar' : 'Activar'}" data-id="${row.formulacion_id}">
                        ${row.formulacion_estado ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
                    </button>`;

                            return `<div class="d-flex gap-1">${btnEdit}${btnToggleEstado}</div>`;
                        }
                    }
                ]
            });

            // Filtrar por estado
            $('#filterEstado').on('change', function() {
                table.ajax.reload();
            });

            // Abrir modal para nueva formulación
            $('#btnNewFormulacion').click(function() {
                isEdit = false;
                $('#formulacionModalLabel').text('Nueva Formulación');
                $('#formulacionForm')[0].reset();
                $('#formulacionId').val('');
                clearValidationErrors();
                formulacionModal.show();
            });

            // Abrir modal para editar formulación
            $('#formulacionesTable').on('click', '.btn-edit', function() {
                isEdit = true;
                clearValidationErrors();
                let id = $(this).data('id');

                $('#formulacionModalLabel').text('Editar Formulación');
                $('#formulacionForm')[0].reset();
                $('#formulacionId').val(id);

                $.get(`/formulacion/${id}`, function(data) {
                    $('#formulacion_nombre').val(data.formulacion_nombre);
                    $('#formulacion_abreviatura').val(data.formulacion_abreviatura);
                    formulacionModal.show();
                });
            });

            // Limpiar errores de validación
            function clearValidationErrors() {
                $('#formulacionForm').find('.is-invalid').removeClass('is-invalid');
                $('#formulacionForm').find('.invalid-feedback').text('');
            }

            // Guardar formulación (crear/editar)
            // Guardar formulación (crear/editar) con UX mejorada
            $('#formulacionForm').submit(function(e) {
                e.preventDefault();

                let $btn = $('#btnSaveFormulacion');
                let originalContent = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');

                clearValidationErrors();

                let id = $('#formulacionId').val();
                let url = isEdit ? `/formulacion/${id}` : '/formulacion';
                let method = isEdit ? 'PATCH' : 'POST';

                let formData = new FormData(this);
                if (isEdit) formData.append('_method', 'PATCH');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $btn.html('<i class="fa fa-check text-white"></i> Guardado');

                        setTimeout(() => {
                            formulacionModal.hide();
                            table.ajax.reload(null, false);
                            $btn.html(originalContent).prop('disabled', false);
                            $('#formulacionForm')[0].reset();
                        }, 600);
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
            $('#formulacionesTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado?')) return;

                let $btn = $(this);
                let id = $btn.data('id');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span>');

                $.ajax({
                    url: `/formulacion/${id}`,
                    method: 'DELETE',
                    success: function(res) {
                        //alert(res.message);
                        table.ajax.reload(null, false);
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
