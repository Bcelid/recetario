@extends('layouts.app')

@section('title', 'Gestión de Malezas')

@section('content')

<h2>Gestión de Malezas</h2>

<div class="mb-3 d-flex align-items-center gap-3">
    <label for="filterEstado" class="form-label mb-0">Estado:</label>
    <select id="filterEstado" class="form-select" style="width: 150px;">
        <option value="all" selected>Todos</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
    </select>

    <button class="btn btn-success ms-auto" id="btnNewMaleza">Nueva Maleza</button>
</div>

<table id="malezasTable" class="display table table-striped" style="width:100%">
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

<!-- Modal Crear/Editar Maleza -->
<div class="modal fade" id="malezaModal" tabindex="-1" aria-labelledby="malezaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="malezaForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="malezaModalLabel">Nueva Maleza</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="malezaId" name="malezaId">

                    <div class="mb-3">
                        <label for="nombre_maleza" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre_maleza" name="maleza_nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="nombre_cientifico" class="form-label">Nombre Científico *</label>
                        <input type="text" class="form-control" id="nombre_cientifico" name="maleza_cientifico" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="detalle" class="form-label">Detalle</label>
                        <textarea class="form-control" id="detalle" name="maleza_detalle" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveMaleza">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let malezaModal = new bootstrap.Modal(document.getElementById('malezaModal'));
    let isEdit = false;

    let table = $('#malezasTable').DataTable({
        ajax: {
            url: '/maleza',
            dataSrc: '',
            data: function(d) {
                d.estado = $('#filterEstado').val();
            }
        },
        columns: [
            { data: 'maleza_id' },
            { data: 'maleza_nombre' },
            { data: 'maleza_cientifico' },
            { data: 'maleza_detalle' },
            {
                data: 'maleza_estado',
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
                    const btnEdit = `<button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.maleza_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`;

                    const btnToggleEstado = `<button class="btn btn-sm ${row.maleza_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" title="${row.maleza_estado ? 'Desactivar' : 'Activar'}" data-id="${row.maleza_id}">
                        ${row.maleza_estado ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
                    </button>`;

                    return `<div class="d-flex gap-1">${btnEdit}${btnToggleEstado}</div>`;
                }
            }
        ]
    });

    $('#filterEstado').on('change', function() {
        table.ajax.reload();
    });

    $('#btnNewMaleza').click(function() {
        isEdit = false;
        $('#malezaModalLabel').text('Nueva Maleza');
        $('#malezaForm')[0].reset();
        $('#malezaId').val('');
        clearValidationErrors();
        malezaModal.show();
    });

    $('#malezasTable').on('click', '.btn-edit', function() {
        isEdit = true;
        clearValidationErrors();
        let id = $(this).data('id');

        $('#malezaModalLabel').text('Editar Maleza');
        $('#malezaForm')[0].reset();
        $('#malezaId').val(id);

        $.get(`/maleza/${id}`, function(data) {
            $('#nombre_maleza').val(data.maleza_nombre);
            $('#nombre_cientifico').val(data.maleza_cientifico);
            $('#detalle').val(data.maleza_detalle);
            malezaModal.show();
        });
    });

    function clearValidationErrors() {
        $('#malezaForm').find('.is-invalid').removeClass('is-invalid');
        $('#malezaForm').find('.invalid-feedback').text('');
    }

    $('#malezaForm').submit(function(e) {
        e.preventDefault();
        clearValidationErrors();

        let id = $('#malezaId').val();
        let url = isEdit ? `/maleza/${id}` : '/maleza';
        let method = isEdit ? 'PATCH' : 'POST';

        let formData = {
            maleza_nombre: $('#nombre_maleza').val(),
            maleza_cientifico: $('#nombre_cientifico').val(),
            maleza_detalle: $('#detalle').val(),
        };

        if (method === 'PATCH') {
            formData._method = 'PATCH';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(res) {
                malezaModal.hide();
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

    $('#malezasTable').on('click', '.btn-toggle-estado', function() {
        if (!confirm('¿Está seguro de cambiar el estado de la maleza?')) return;

        let id = $(this).data('id');

        $.ajax({
            url: `/maleza/${id}`,
            method: 'DELETE',
            success: function(res) {
                alert(res.message);
                table.ajax.reload(null, false);
            },
            error: function() {
                alert('Error al cambiar estado de la maleza');
            }
        });
    });
});
</script>
@endsection
