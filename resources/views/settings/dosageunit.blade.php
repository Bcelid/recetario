@extends('layouts.app')

@section('title', 'Unidades de Medida de Dosificación')

@section('content')

<h2>Unidades de Medida de Dosificación</h2>

<div class="mb-3 d-flex align-items-center gap-3">
    <label for="filterEstado" class="form-label mb-0">Estado:</label>
    <select id="filterEstado" class="form-select" style="width: 150px;">
        <option value="all" selected>Todos</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
    </select>

    <button class="btn btn-success ms-auto" id="btnNewUnidad">Nueva Unidad</button>
</div>

<table id="unidadesTable" class="display table table-striped" style="width:100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Representación</th>
            <th>Detalle</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        {{-- Se llenará por Ajax --}}
    </tbody>
</table>

<!-- Modal Crear/Editar Unidad -->
<div class="modal fade" id="unidadModal" tabindex="-1" aria-labelledby="unidadModalLabel" aria-hidden="true">
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
                        <label for="unidad_representacion" class="form-label">Representación *</label>
                        <input type="text" class="form-control" id="unidad_representacion" name="unidad_medida_dosificacion_representacion" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="unidad_detalle" class="form-label">Detalle</label>
                        <input type="text" class="form-control" id="unidad_detalle" name="unidad_medida_dosificacion_detalle">
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
            url: '/unidad-medida-dosificacion',
            dataSrc: '',
            data: function(d) {
                d.estado = $('#filterEstado').val();
            }
        },
        columns: [
            { data: 'unidad_medida_dosificacion_id' },
            { data: 'unidad_medida_dosificacion_representacion' },
            { data: 'unidad_medida_dosificacion_detalle' },
            { 
                data: 'unidad_medida_dosificacion_estado',
                render: function(data) {
                    return data == 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-secondary">Inactivo</span>';
                }
            },
            { 
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const btnEdit = `<button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.unidad_medida_dosificacion_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`;

                    const btnToggleEstado = `<button class="btn btn-sm ${row.unidad_medida_dosificacion_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" title="${row.unidad_medida_dosificacion_estado ? 'Desactivar' : 'Activar'}" data-id="${row.unidad_medida_dosificacion_id}">
                        ${row.unidad_medida_dosificacion_estado ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
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
        $('#unidadModalLabel').text('Nueva Unidad de Medida de Dosificación');
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

        $('#unidadModalLabel').text('Editar Unidad de Medida de Dosificación');
        $('#unidadForm')[0].reset();
        $('#unidadId').val(id);

        $.get(`/unidad-medida-dosificacion/${id}`, function(data) {
            $('#unidad_representacion').val(data.unidad_medida_dosificacion_representacion);
            $('#unidad_detalle').val(data.unidad_medida_dosificacion_detalle);
            unidadModal.show();
        });
    });

    // Limpiar errores de validación
    function clearValidationErrors() {
        $('#unidadForm').find('.is-invalid').removeClass('is-invalid');
        $('#unidadForm').find('.invalid-feedback').text('');
    }

    // Guardar unidad (crear/editar)
    $('#unidadForm').submit(function(e) {
        e.preventDefault();
        clearValidationErrors();

        let id = $('#unidadId').val();
        let url = isEdit ? `/unidad-medida-dosificacion/${id}` : '/unidad-medida-dosificacion';
        let method = isEdit ? 'PATCH' : 'POST';

        let formData = {
            unidad_medida_dosificacion_representacion: $('#unidad_representacion').val(),
            unidad_medida_dosificacion_detalle: $('#unidad_detalle').val(),
        };

        if (method === 'PATCH') {
            formData._method = 'PATCH';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(res) {
                unidadModal.hide();
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

    // Cambiar estado (activar/desactivar)
    $('#unidadesTable').on('click', '.btn-toggle-estado', function() {
        if(!confirm('¿Está seguro de cambiar el estado?')) return;

        let id = $(this).data('id');

        $.ajax({
            url: `/unidad-medida-dosificacion/${id}`,
            method: 'DELETE',
            success: function(res) {
                alert(res.message);
                table.ajax.reload(null, false);
            },
            error: function() {
                alert('Error al cambiar estado');
            }
        });
    });

});
</script>
@endsection
