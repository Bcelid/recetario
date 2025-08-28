@extends('layouts.app')

@section('title', 'Gestión de Productos')

@section('content')

    <h2>Gestión de Productos</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label for="filterEstado" class="form-label mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all">Todos</option>
            <option value="1"selected>Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <label for="filterTipo" class="form-label mb-0">Tipo:</label>
        <select id="filterTipo" class="form-select" style="width: 150px;">
            <option value="all" selected>Todos</option>
            <option value="0">Agrícola</option>
            <option value="1">Veterinario</option>
        </select>

        <button class="btn btn-success ms-auto" id="btnNewProducto">Nuevo Producto</button>
    </div>

    <div class="table-responsive">
    <table id="productosTable" class="display table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Nombre</th>
                <th>Concentración</th>
                <th>Presentación</th>
                <th>Formulación</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // Inicializar el botón de nuevo producto

            $('#productosTable').on('click', '.btn-toggle-estado', function() {
                if (!confirm('¿Está seguro de cambiar el estado del producto?')) return;

                
                let $btn = $(this);
                let id = $btn.data('id');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span>');

                $.ajax({
                    url: `/producto/${id}`,
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

            // Evento para ver detalle
            $('#productosTable').on('click', '.btn-ver-detalle', function() {
                const id = $(this).data('id');
                window.location.href = `/product/show/${id}`;
            });
            $('#btnNewProducto').on('click', function() {
                window.location.href =
                    "{{ route('product.product') }}"; // Redirigir a la vista de creación de producto
            });

            $('#productosTable').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                window.location.href = `/producto/${id}`;
            });



            let table = $('#productosTable').DataTable({
                ajax: {
                    url: '/producto',
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val();
                        d.tipo = $('#filterTipo').val();
                    }
                },
                columns: [{
                        data: 'producto_tipo',
                        render: function(data) {
                            if (data == 1) {
                                return `
                <span class="badge bg-primary align-items-center justify-content-center gap-1 text-nowrap" style="min-width: 50px;">
                    <i class="fa-solid fa-cow"></i> Veterinario
                </span>`;
                            } else {
                                return `
                <span class="badge bg-success align-items-center justify-content-center gap-1 text-nowrap" style="min-width: 50px;">
                    <i class="fa-solid fa-seedling"></i> Agrícola
                </span>`;
                            }
                        }
                    },
                    {
                        data: 'producto_nombre'
                    },
                    {
                        data: 'producto_concentracion'
                    },
                    {
                        data: 'presentacion_unidad'
                    },
                    {
                        data: 'formulacion_abreviatura'
                    },
                    {
                        data: 'producto_estado',
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
        <button class="btn btn-sm btn-info btn-ver-detalle" data-id="${data.producto_id}">
            <i class="fa-solid fa-eye"></i>
        </button>

        <button class="btn btn-sm btn-primary btn-edit" data-id="${data.producto_id}">
            <i class="fa-solid fa-pen-to-square"></i>
        </button>

        <button class="btn btn-sm ${data.producto_estado == 1 ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.producto_id}">
            ${data.producto_estado == 1 ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
        </button>
    </div>`;
                        }

                    }
                ]
            });
            $('#filterTipo, #filterEstado').on('change', function() {
                table.ajax.reload();
            });




        });
    </script>
@endsection
