@extends('layouts.app')

@section('title', 'Ingredientes Activos')

@section('content')

<h2>Ingredientes Activos</h2>

<div class="mb-3 d-flex align-items-center gap-3">
    <label for="filterEstado" class="form-label mb-0">Estado:</label>
    <select id="filterEstado" class="form-select" style="width: 150px;">
        <option value="all" selected>Todos</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
    </select>

    <button class="btn btn-success ms-auto" id="btnNewIngrediente">Nuevo Ingrediente</button>
</div>

<table id="ingredientesTable" class="display table table-striped" style="width:100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre Ingrediente</th>
            <th>Detalle</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        {{-- Se llenará por Ajax --}}
    </tbody>
</table>

<!-- Modal Crear/Editar Ingrediente -->
<div class="modal fade" id="ingredienteModal" tabindex="-1" aria-labelledby="ingredienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="ingredienteForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ingredienteModalLabel">Nuevo Ingrediente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" id="ingredienteId" name="ingredienteId">

                    <div class="mb-3">
                        <label for="ingrediente_nombre" class="form-label">Nombre Ingrediente *</label>
                        <input type="text" class="form-control" id="ingrediente_nombre" name="ingrediente_activo_nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="ingrediente_detalle" class="form-label">Detalle</label>
                        <textarea class="form-control" id="ingrediente_detalle" name="ingrediente_activo_detalle" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveIngrediente">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let ingredienteModal = new bootstrap.Modal(document.getElementById('ingredienteModal'));
    let isEdit = false;

    // Inicializar DataTable
    let table = $('#ingredientesTable').DataTable({
        ajax: {
            url: '/ingredientes-activos',
            dataSrc: '',
            data: function(d) {
                d.estado = $('#filterEstado').val();
            }
        },
        columns: [
            { data: 'ingrediente_activo_id' },
            { data: 'ingrediente_activo_nombre' },
            { 
                data: 'ingrediente_activo_detalle',
                render: function(data) {
                    return data ? data : '<em>Sin detalle</em>';
                }
            },
            { 
                data: 'ingrediente_activo_estado',
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
                    const btnEdit = `<button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.ingrediente_activo_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`;

                    const btnToggleEstado = `<button class="btn btn-sm ${row.ingrediente_activo_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" title="${row.ingrediente_activo_estado ? 'Desactivar' : 'Activar'}" data-id="${row.ingrediente_activo_id}">
                        ${row.ingrediente_activo_estado ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
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

    // Abrir modal para nuevo ingrediente
    $('#btnNewIngrediente').click(function() {
        isEdit = false;
        $('#ingredienteModalLabel').text('Nuevo Ingrediente Activo');
        $('#ingredienteForm')[0].reset();
        $('#ingredienteId').val('');
        clearValidationErrors();
        ingredienteModal.show();
    });

    // Abrir modal para editar ingrediente
    $('#ingredientesTable').on('click', '.btn-edit', function() {
        isEdit = true;
        clearValidationErrors();
        let id = $(this).data('id');

        $('#ingredienteModalLabel').text('Editar Ingrediente Activo');
        $('#ingredienteForm')[0].reset();
        $('#ingredienteId').val(id);

        $.get(`/ingredientes-activos/${id}`, function(data) {
            $('#ingrediente_nombre').val(data.ingrediente_activo_nombre);
            $('#ingrediente_detalle').val(data.ingrediente_activo_detalle);
            ingredienteModal.show();
        });
    });

    // Limpiar errores de validación
    function clearValidationErrors() {
        $('#ingredienteForm').find('.is-invalid').removeClass('is-invalid');
        $('#ingredienteForm').find('.invalid-feedback').text('');
    }

    // Guardar ingrediente (crear/editar)
    $('#ingredienteForm').submit(function(e) {
        e.preventDefault();
        clearValidationErrors();

        let id = $('#ingredienteId').val();
        let url = isEdit ? `/ingredientes-activos/${id}` : '/ingredientes-activos';
        let method = isEdit ? 'PATCH' : 'POST';

        let formData = {
            ingrediente_activo_nombre: $('#ingrediente_nombre').val(),
            ingrediente_activo_detalle: $('#ingrediente_detalle').val(),
        };

        if (method === 'PATCH') {
            formData._method = 'PATCH';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(res) {
                ingredienteModal.hide();
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
    $('#ingredientesTable').on('click', '.btn-toggle-estado', function() {
        if(!confirm('¿Está seguro de cambiar el estado?')) return;

        let id = $(this).data('id');

        $.ajax({
            url: `/ingredientes-activos/${id}`,
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
