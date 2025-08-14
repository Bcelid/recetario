@extends('layouts.app')

@section('title', 'Categorías Técnicas')

@section('content')

<h2>Categorías Técnicas</h2>

<div class="mb-3 d-flex align-items-center gap-3">
    <label for="filterEstado" class="form-label mb-0">Estado:</label>
    <select id="filterEstado" class="form-select" style="width: 150px;">
        <option value="all" selected>Todos</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
    </select>

    <button class="btn btn-success ms-auto" id="btnNewCategoria">Nueva Categoría</button>
</div>

<table id="categoriasTable" class="display table table-striped" style="width:100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre Categoría</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        {{-- Se llenará por Ajax --}}
    </tbody>
</table>

<!-- Modal Crear/Editar Categoría -->
<div class="modal fade" id="categoriaModal" tabindex="-1" aria-labelledby="categoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="categoriaForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoriaModalLabel">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" id="categoriaId" name="categoriaId">

                    <div class="mb-3">
                        <label for="nombre_categoria" class="form-label">Nombre Categoría *</label>
                        <input type="text" class="form-control" id="nombre_categoria" name="tecnico_categoria_nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCategoria">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let categoriaModal = new bootstrap.Modal(document.getElementById('categoriaModal'));
    let isEdit = false;

    // Inicializar DataTable
    let table = $('#categoriasTable').DataTable({
        ajax: {
            url: '/tecnico-categorias',
            dataSrc: '',
            data: function(d) {
                d.estado = $('#filterEstado').val();
            }
        },
        columns: [
            { data: 'tecnico_categoria_id' },
            { data: 'tecnico_categoria_nombre' },
            { 
                data: 'tecnico_categoria_estado',
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
                    const btnEdit = `<button class="btn btn-primary btn-sm btn-edit" title="Editar" data-id="${row.tecnico_categoria_id}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>`;

                    const btnToggleEstado = `<button class="btn btn-sm ${row.tecnico_categoria_estado ? 'btn-danger' : 'btn-success'} btn-toggle-estado" title="${row.tecnico_categoria_estado ? 'Desactivar' : 'Activar'}" data-id="${row.tecnico_categoria_id}">
                        ${row.tecnico_categoria_estado ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
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

    // Abrir modal para nueva categoría
    $('#btnNewCategoria').click(function() {
        isEdit = false;
        $('#categoriaModalLabel').text('Nueva Categoría');
        $('#categoriaForm')[0].reset();
        $('#categoriaId').val('');
        clearValidationErrors();
        categoriaModal.show();
    });

    // Abrir modal para editar categoría
    $('#categoriasTable').on('click', '.btn-edit', function() {
        isEdit = true;
        clearValidationErrors();
        let id = $(this).data('id');

        $('#categoriaModalLabel').text('Editar Categoría');
        $('#categoriaForm')[0].reset();
        $('#categoriaId').val(id);

        // Obtener datos para editar
        $.get(`/tecnico-categorias/${id}`, function(data) {
            $('#nombre_categoria').val(data.tecnico_categoria_nombre);
            categoriaModal.show();
        });
    });

    // Limpiar errores validación
    function clearValidationErrors() {
        $('#categoriaForm').find('.is-invalid').removeClass('is-invalid');
        $('#categoriaForm').find('.invalid-feedback').text('');
    }

    // Enviar formulario crear/editar
    $('#categoriaForm').submit(function(e) {
        e.preventDefault();
        clearValidationErrors();

        let id = $('#categoriaId').val();
        let url = isEdit ? `/tecnico-categorias/${id}` : '/tecnico-categorias';
        let method = isEdit ? 'PATCH' : 'POST';

        let formData = {
            tecnico_categoria_nombre: $('#nombre_categoria').val(),
        };

        if (method === 'PATCH') {
            formData._method = 'PATCH';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(res) {
                categoriaModal.hide();
                table.ajax.reload(null, false); // recarga sin resetear paginación
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
    $('#categoriasTable').on('click', '.btn-toggle-estado', function() {
        if(!confirm('¿Está seguro de cambiar el estado?')) return;

        let id = $(this).data('id');

        $.ajax({
            url: `/tecnico-categorias/${id}`,
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
