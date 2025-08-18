@extends('layouts.app')

@section('title', 'Gestión de Cultivos')

@section('content')

<h2>Gestión de Cultivos</h2>

<div class="mb-3 d-flex align-items-center gap-3">
    <label for="filterEstado" class="form-label mb-0">Estado:</label>
    <select id="filterEstado" class="form-select" style="width: 150px;">
        <option value="all" >Todos</option>
        <option value="1"selected>Activo</option>
        <option value="0">Inactivo</option>
    </select>

    <button class="btn btn-success ms-auto" id="btnNewCultivo">Nuevo Cultivo</button>
</div>
<div class="table-responsive">
<table id="cultivosTable" class="display table table-striped" style="width:100%">
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
</div>
<!-- Modal Crear/Editar Cultivo -->
<div class="modal fade" id="cultivoModal" tabindex="-1" aria-labelledby="cultivoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="cultivoForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cultivoModalLabel">Nuevo Cultivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cultivoId" name="cultivoId">

                    <div class="mb-3">
                        <label for="nombre_cultivo" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre_cultivo" name="cultivo_nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="nombre_cientifico" class="form-label">Nombre Científico *</label>
                        <input type="text" class="form-control" id="nombre_cientifico" name="cultivo_cientifico" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="detalle" class="form-label">Detalle</label>
                        <textarea class="form-control" id="detalle" name="cultivo_detalle" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCultivo">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let cultivoModal = new bootstrap.Modal(document.getElementById('cultivoModal'));
    let isEdit = false;

    // Inicializar DataTable
    let table = $('#cultivosTable').DataTable({
        ajax: {
            url: '/cultivos',
            dataSrc: '',
            data: function(d) {
                d.estado = $('#filterEstado').val();
            }
        },
        columns: [
            { data: 'cultivo_id' },
            { data: 'cultivo_nombre' },
            { data: 'cultivo_cientifico' },
            { data: 'cultivo_detalle' },
            { 
                data: 'cultivo_estado',
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
                    const btnEdit = `<button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.cultivo_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`;

                    const btnToggleEstado = `<button class="btn btn-sm ${row.cultivo_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" title="${row.cultivo_estado ? 'Desactivar' : 'Activar'}" data-id="${row.cultivo_id}">
                        ${row.cultivo_estado ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
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

    // Abrir modal para nuevo cultivo
    $('#btnNewCultivo').click(function() {
        isEdit = false;
        $('#cultivoModalLabel').text('Nuevo Cultivo');
        $('#cultivoForm')[0].reset();
        $('#cultivoId').val('');
        clearValidationErrors();
        cultivoModal.show();
    });

    // Abrir modal para editar cultivo
    $('#cultivosTable').on('click', '.btn-edit', function() {
        isEdit = true;
        clearValidationErrors();
        let id = $(this).data('id');

        $('#cultivoModalLabel').text('Editar Cultivo');
        $('#cultivoForm')[0].reset();
        $('#cultivoId').val(id);

        $.get(`/cultivos/${id}`, function(data) {
            $('#nombre_cultivo').val(data.cultivo_nombre);
            $('#nombre_cientifico').val(data.cultivo_cientifico);
            $('#detalle').val(data.cultivo_detalle);
            cultivoModal.show();
        });
    });

    // Limpiar errores
    function clearValidationErrors() {
        $('#cultivoForm').find('.is-invalid').removeClass('is-invalid');
        $('#cultivoForm').find('.invalid-feedback').text('');
    }

    // Guardar cultivo (nuevo o editado)
    $('#cultivoForm').submit(function(e) {
        e.preventDefault();
        clearValidationErrors();

        let id = $('#cultivoId').val();
        let url = isEdit ? `/cultivos/${id}` : '/cultivos';
        let method = isEdit ? 'PATCH' : 'POST';

        let formData = {
            cultivo_nombre: $('#nombre_cultivo').val(),
            cultivo_cientifico: $('#nombre_cientifico').val(),
            cultivo_detalle: $('#detalle').val(),
        };

        if (method === 'PATCH') {
            formData._method = 'PATCH';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(res) {
                cultivoModal.hide();
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

    // Cambiar estado
    $('#cultivosTable').on('click', '.btn-toggle-estado', function() {
        if(!confirm('¿Está seguro de cambiar el estado del cultivo?')) return;

        let id = $(this).data('id');

        $.ajax({
            url: `/cultivos/${id}`,
            method: 'DELETE',
            success: function(res) {
                alert(res.message);
                table.ajax.reload(null, false);
            },
            error: function() {
                alert('Error al cambiar estado del cultivo');
            }
        });
    });
});
</script>
@endsection
