@extends('layouts.app')

@section('title', 'Propietarios de Almacén')

@section('content')

<h2>Propietarios de Almacén</h2>

<div class="mb-3 d-flex align-items-center gap-3">
    <label for="filterEstado" class="form-label mb-0">Estado:</label>
    <select id="filterEstado" class="form-select" style="width: 150px;">
        <option value="all" selected>Todos</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
    </select>

    <button class="btn btn-success ms-auto" id="btnNewPropietario">Nuevo Propietario</button>
</div>

<table id="propietariosTable" class="display table table-striped" style="width:100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Dirección</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        {{-- Llenado por Ajax --}}
    </tbody>
</table>

<!-- Modal Crear/Editar Propietario -->
<div class="modal fade" id="propietarioModal" tabindex="-1" aria-labelledby="propietarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="propietarioForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="propietarioModalLabel">Nuevo Propietario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" id="propietarioId" name="propietarioId">

                    <div class="mb-3">
                        <label for="nombre_propietario" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre_propietario" name="propietario_almacen_nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="apellido_propietario" class="form-label">Apellido *</label>
                        <input type="text" class="form-control" id="apellido_propietario" name="propietario_almacen_apellido" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="direccion_propietario" class="form-label">Dirección *</label>
                        <input type="text" class="form-control" id="direccion_propietario" name="propietario_almacen_direccion" required>
                        <div class="invalid-feedback"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnSavePropietario">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let propietarioModal = new bootstrap.Modal(document.getElementById('propietarioModal'));
    let isEdit = false;

    // Inicializar DataTable
    let table = $('#propietariosTable').DataTable({
        ajax: {
            url: '/propietario-almacen',
            dataSrc: '',
            data: function(d) {
                d.estado = $('#filterEstado').val();
            }
        },
        columns: [
            { data: 'propietario_almacen_id' },
            { data: 'propietario_almacen_nombre' },
            { data: 'propietario_almacen_apellido' },
            { data: 'propietario_almacen_direccion' },
            { 
                data: 'propietario_almacen_estado',
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
                    const btnEdit = `<button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.propietario_almacen_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`;

                    const btnToggleEstado = `<button class="btn btn-sm ${row.propietario_almacen_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" title="${row.propietario_almacen_estado ? 'Desactivar' : 'Activar'}" data-id="${row.propietario_almacen_id}">
                        ${row.propietario_almacen_estado ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
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

    // Abrir modal para nuevo propietario
    $('#btnNewPropietario').click(function() {
        isEdit = false;
        $('#propietarioModalLabel').text('Nuevo Propietario');
        $('#propietarioForm')[0].reset();
        $('#propietarioId').val('');
        clearValidationErrors();
        propietarioModal.show();
    });

    // Abrir modal para editar
    $('#propietariosTable').on('click', '.btn-edit', function() {
        isEdit = true;
        clearValidationErrors();
        let id = $(this).data('id');

        $('#propietarioModalLabel').text('Editar Propietario');
        $('#propietarioForm')[0].reset();
        $('#propietarioId').val(id);

        $.get(`/propietario-almacen/${id}`, function(data) {
            $('#nombre_propietario').val(data.propietario_almacen_nombre);
            $('#apellido_propietario').val(data.propietario_almacen_apellido);
            $('#direccion_propietario').val(data.propietario_almacen_direccion);
            propietarioModal.show();
        });
    });

    function clearValidationErrors() {
        $('#propietarioForm').find('.is-invalid').removeClass('is-invalid');
        $('#propietarioForm').find('.invalid-feedback').text('');
    }

    // Guardar
    $('#propietarioForm').submit(function(e) {
        e.preventDefault();
        clearValidationErrors();

        let id = $('#propietarioId').val();
        let url = isEdit ? `/propietario-almacen/${id}` : '/propietario-almacen';
        let method = isEdit ? 'PATCH' : 'POST';

        let formData = {
            propietario_almacen_nombre: $('#nombre_propietario').val(),
            propietario_almacen_apellido: $('#apellido_propietario').val(),
            propietario_almacen_direccion: $('#direccion_propietario').val()
        };

        if (method === 'PATCH') {
            formData._method = 'PATCH';
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(res) {
                propietarioModal.hide();
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
    $('#propietariosTable').on('click', '.btn-toggle-estado', function() {
        if(!confirm('¿Está seguro de cambiar el estado?')) return;

        let id = $(this).data('id');

        $.ajax({
            url: `/propietario-almacen/${id}`,
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
