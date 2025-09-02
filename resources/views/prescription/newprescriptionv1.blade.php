@extends('layouts.app')

@section('title', 'Generar Receta')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <h4>Generar Receta</h4>

    <!-- Datos generales del lote -->
    <div class="row mb-3">
        <div class="col-md-2">
            <label class="form-label">Tipo de Receta</label>
            <select id="tipoRecetaSelect" class="form-select" required>
                <option value="" disabled selected>Seleccione tipo</option>
                <option value="0">Agrícola</option>
                <option value="1">Veterinaria</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Técnico</label>
            <select id="tecnicoSelect" class="form-select" required></select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Almacén</label>
            <select id="almacenSelect" class="form-select" required></select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Fecha Lote</label>
            <input type="date" id="fechaLote" class="form-control" required>
        </div>
    </div>

    <hr>

    <!-- Sección de recetas -->
    <h5>Recetas del lote</h5>
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">Fecha de la receta</label>
            <input type="date" id="fechaRecetaInput" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" id="btnAgregarReceta">Agregar Receta</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="tablaRecetas">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha de Receta</th>
                    <th>Productos</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Modal para productos -->
    <!-- Modal para agregar productos -->
    <div class="modal fade" id="productoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Productos a la Receta</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label>Cantidad</label>
                            <input type="number" id="cantidadInput" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label>Producto</label>
                            <div class="input-group">
                                <select id="productoSelect" class="form-select">
                                    <option value="">-- Seleccionar producto --</option>
                                </select>
                                <button class="btn btn-secondary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#productoBuscarModal">
                                    Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-success w-100" id="btnAgregarProducto">Agregar</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablaProductos">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Producto</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal Buscar Producto -->
    <div class="modal fade" id="productoBuscarModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Producto</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="mensajeSeleccionTipo" class="text-center text-muted my-3">
                        Seleccione un tipo de receta para cargar los productos.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaModalProductos">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Formulación</th>
                                    <th>Presentación</th>
                                    <th>Concentración</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Botón final -->
    <div class="mt-4 text-end">
        <button class="btn btn-success" id="btnGenerarLote">Generar Lote</button>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let recetas = []; // array de recetas
            let recetaSeleccionada = null;

            // Cargar técnicos y almacenes
            $.get('/tecnico', function(data) {
                let select = $('#tecnicoSelect').append('<option value="">Seleccione</option>');
                data.forEach(t => select.append(
                    `<option value="${t.tecnico_id}">${t.tecnico_nombre} ${t.tecnico_apellido}</option>`
                ));
            });

            $.get('/almacen', function(data) {
                let select = $('#almacenSelect').append('<option value="">Seleccione</option>');
                data.forEach(a => select.append(
                    `<option value="${a.almacen_id}">${a.almacen_nombre}</option>`));
            });

            // Cargar productos por tipo de receta
            $('#tipoRecetaSelect').on('change', function() {
                let tipo = $(this).val();

                // Limpiar select de productos y tabla del modal
                $('#productoSelect').empty().append('<option value="">-- Seleccionar producto --</option>');


                // Destruir DataTable si ya está inicializado
                if ($.fn.DataTable.isDataTable('#tablaModalProductos')) {
                    $('#tablaModalProductos').DataTable().clear().destroy();
                }

                // Vaciar el tbody antes de agregar nuevos datos
                $('#tablaModalProductos tbody').empty();
                $('#tablaRecetas tbody').empty();

                recetas = [];
                recetaSeleccionada = null;

                if (!tipo) {
                    $('#mensajeSeleccionTipo').show();
                    return;
                }

                // Obtener productos
                $.get(`/producto/tipo/${tipo}`, function(productos) {

                    if (productos.length === 0) {
                        $('#mensajeSeleccionTipo').text(
                            'No se encontraron productos para este tipo.').show();
                    } else {
                        $('#mensajeSeleccionTipo').hide();
                    }
                    productos.forEach(p => {
                        // Agregar al select de productos
                        $('#productoSelect').append(
                            `<option value="${p.producto_id}" data-nombre="${p.nombre}">${p.nombre} ${p.presentacion}</option>`
                        );

                        // Agregar a la tabla del modal
                        $('#tablaModalProductos tbody').append(`
                <tr>
                    <td>${p.nombre}</td>
                    <td>${p.formulacion}</td>
                    <td>${p.presentacion}</td>
                    <td>${p.concentracion}</td>
                    <td><button class="btn btn-sm btn-success seleccionar-modal" data-id="${p.producto_id}" data-nombre="${p.nombre}" data-bs-dismiss="modal">Seleccionar</button></td>
                </tr>
            `);
                    });

                    // Inicializar DataTable después de agregar los productos
                    $('#tablaModalProductos').DataTable({
                        responsive: true,
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                        }
                    });
                });
            });

            // Agregar receta
            $('#btnAgregarReceta').on('click', function() {
                const fechaReceta = $('#fechaRecetaInput').val();
                if (!fechaReceta) {
                    alert('Debes seleccionar la fecha de la receta');
                    return;
                }
                let index = recetas.length;
                recetas.push({
                    fecha_emision: fechaReceta,
                    productos: []
                });
                renderRecetas();
                $('#fechaRecetaInput').val('');
            });

            $(document).on('click', '.seleccionar-modal', function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');

                // Setear en el select del modal principal
                $('#productoSelect').val(id).trigger('change');

                // Cerrar solo el modal de búsqueda
                $('#productoBuscarModal').modal('hide');

                // Reabrir el modal principal después de que se cierre el de búsqueda
                $('#productoBuscarModal').on('hidden.bs.modal', function() {
                    $('#productoModal').modal(
                        'show'); // <-- cambia #modalPrincipal por el id de tu modal principal
                    // Importante: quitar el evento para que no se dispare cada vez
                    $(this).off('hidden.bs.modal');
                });
            });

            // Renderizar tabla de recetas
            function renderRecetas() {
                let tbody = $('#tablaRecetas tbody');
                tbody.empty();
                recetas.forEach((r, i) => {
                    let productosHtml = r.productos.map(p =>
                        `<li>${p.producto_cantidad} - ${p.nombre}</li>`).join(
                        '');
                    tbody.append(`
                <tr>
                    <td>${i+1}</td>
                    <td>${r.fecha_emision}</td>
                    <td><ul>${productosHtml || '<i>Sin productos</i>'}</ul></td>
                    <td>
                        <button class="btn btn-sm btn-primary agregar-productos" data-index="${i}" data-bs-toggle="modal" data-bs-target="#productoModal">Agregar Productos</button>
                        <button class="btn btn-sm btn-danger eliminar-receta" data-index="${i}">Eliminar</button>
                    </td>
                </tr>
            `);
                });
            }

            // Abrir modal de productos
            $(document).on('click', '.agregar-productos', function() {
                recetaSeleccionada = $(this).data('index');
                renderProductos();
            });

            // Renderizar tabla de productos dentro del modal
            function renderProductos() {
                let tbody = $('#tablaProductos tbody');
                tbody.empty();
                if (recetaSeleccionada === null) return;
                recetas[recetaSeleccionada].productos.forEach((p, i) => {
                    tbody.append(`
                <tr>
                    <td>${p.producto_cantidad}</td>
                    <td>${p.nombre}</td>
                    <td><button class="btn btn-danger btn-sm eliminar-producto" data-index="${i}">Eliminar</button></td>
                </tr>
            `);
                });
            }

            // Agregar producto a la receta seleccionada
            $('#btnAgregarProducto').on('click', function() {
                const cantidad = parseFloat($('#cantidadInput').val());
                const productoId = $('#productoSelect').val();
                const productoNombre = $('#productoSelect option:selected').text();

                if (!cantidad || !productoId) {
                    alert('Completa los campos de producto');
                    return;
                }

                let receta = recetas[recetaSeleccionada];

                // Validación para no permitir más de 5 productos en la receta
                if (receta.productos.length >= 5) {
                    alert('No puedes agregar más de 5 productos por receta.');
                    return;
                }

                // Buscar si el producto ya existe en la receta seleccionada
                let productoExistente = receta.productos.find(p => p.producto_id == productoId);

                if (productoExistente) {
                    // Si existe, sumamos la cantidad
                    productoExistente.producto_cantidad = parseFloat(productoExistente.producto_cantidad) +
                        cantidad;
                } else {
                    // Si no existe, lo agregamos
                    receta.productos.push({
                        producto_id: productoId,
                        producto_cantidad: cantidad,
                        nombre: productoNombre
                    });
                }

                // Limpiar inputs y renderizar
                $('#cantidadInput').val('');
                $('#productoSelect').val('');
                renderProductos();
                renderRecetas();
            });



            // Eliminar producto
            $(document).on('click', '.eliminar-producto', function() {
                const index = $(this).data('index');
                recetas[recetaSeleccionada].productos.splice(index, 1);
                renderProductos();
                renderRecetas();
            });

            // Eliminar receta
            $(document).on('click', '.eliminar-receta', function() {
                const index = $(this).data('index');
                recetas.splice(index, 1);
                renderRecetas();
            });

            // Guardar lote completo
            $('#btnGenerarLote').on('click', function() {
                const tipoReceta = $('#tipoRecetaSelect').val();
                const tecnico = $('#tecnicoSelect').val();
                const almacen = $('#almacenSelect').val();
                const fecha = $('#fechaLote').val();

                if (!tipoReceta || !tecnico || !almacen || !fecha) {
                    alert('Completa los campos generales');
                    return;
                }
                if (recetas.length === 0) {
                    alert('Debes agregar al menos una receta con productos');
                    return;
                }

                const payload = {
                    tecnico_id: tecnico,
                    almacen_id: almacen,
                    receta_tipo: tipoReceta,
                    fecha_creacion: fecha,
                    recetas: recetas
                };
                console.log(JSON.stringify(payload, null, 2)); // Para ver el payload antes de enviar
                $('#loadingModal').modal('show');
                $.ajax({
                    url: '/prescriptionv1',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        //alert(response.message);
                        $('#loadingModal').modal('hide');
                        window.location.replace(
                            "{{ route('prescription.list_prescription') }}");
                    },
                    error: function(xhr) {
                        $('#loadingModal').modal('hide');
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            alert('Error: ' + xhr.responseJSON.error);
                        } else {
                            alert('Error inesperado.');
                        }
                    }
                });
            });

        });
    </script>
@endsection
