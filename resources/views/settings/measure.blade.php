@extends('layouts.app')

@section('title', 'Unidades de Medida')

@section('content')

    <h2>Unidades de Medida</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all">Todos</option>
            <option value="1" selected>Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewUnidad">Nueva Unidad</button>
    </div>
    <div class="table-responsive">
        <table id="unidadesTable" class="display table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Detalle</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {{-- Se llenará por Ajax --}}
            </tbody>
        </table>
    </div>
    <!-- Modal Crear/Editar Unidad -->
    <div class="modal fade" id="unidadModal" tabindex="-1" aria-labelledby="unidadModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <form id="unidadForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="unidadModalLabel">Nueva Unidad</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="unidadId" name="unidadId">

                        <div class="mb-3">
                            <label for="unidad_detalle" class="form-label">Detalle *</label>
                            <input type="text" class="form-control" id="unidad_detalle" name="unidad_medida_detalle"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveUnidad">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let unidadModal = new bootstrap.Modal(document.getElementById('unidadModal'));
            let isEdit = false;

            // Inicializar DataTable
            let table = $('#unidadesTable').DataTable({
                ajax: {
                    url: '/unidad-medida',
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val();
                    }
                },
                columns: [{
                        data: 'unidad_medida_id'
                    },
                    {
                        data: 'unidad_medida_detalle'
                    },
                    {
                        data: 'unidad_medida_estado',
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
                            const btnEdit = `<button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.unidad_medida_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`;

                            const btnToggleEstado = `<button class="btn btn-sm ${row.unidad_medida_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" title="${row.unidad_medida_estado ? 'Desactivar' : 'Activar'}" data-id="${row.unidad_medida_id}">
                        ${row.unidad_medida_estado ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
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

            // Abrir modal para nueva unidad
            $('#btnNewUnidad').click(function() {
                isEdit = false;
                $('#unidadModalLabel').text('Nueva Unidad de Medida');
                $('#unidadForm')[0].reset();
                $('#unidadId').val('');
                clearValidationErrors();
                unidadModal.show();
            });

            // Abrir modal para editar unidad
            $('#unidadesTable').on('click', '.btn-edit', function() {
                isEdit = true;
                clearValidationErrors();
                let id = $(this).data('id');

                $('#unidadModalLabel').text('Editar Unidad de Medida');
                $('#unidadForm')[0].reset();
                $('#unidadId').val(id);

                $.get(`/unidad-medida/${id}`, function(data) {
                    $('#unidad_detalle').val(data.unidad_medida_detalle);
                    unidadModal.show();
                });
            });

            // Limpiar errores de validación
            function clearValidationErrors() {
                $('#unidadForm').find('.is-invalid').removeClass('is-invalid');
                $('#unidadForm').find('.invalid-feedback').text('');
            }

            // Guardar unidad (crear/editar)
            // Guardar unidad (crear/editar) con spinner y mejor UX
            $('#unidadForm').submit(function(e) {
                e.preventDefault();

                let $btn = $('#btnSaveUnidad');
                let originalContent = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');

                clearValidationErrors();

                let id = $('#unidadId').val();
                let url = isEdit ? `/unidad-medida/${id}` : '/unidad-medida';
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
                            unidadModal.hide();
                            table.ajax.reload(null, false);
                            $btn.html(originalContent).prop('disabled', false);
                            $('#unidadForm')[0].reset();
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
            $('#unidadesTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado?')) return;

                let $btn = $(this);
                let id = $btn.data('id');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span>');


                $.ajax({
                    url: `/unidad-medida/${id}`,
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
