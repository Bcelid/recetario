@extends('layouts.app')

@section('title', 'Generar Receta')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <h4>Generar Receta</h4>

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
            <label class="form-label">Fecha</label>
            <input type="date" id="fechaLote" class="form-control" required>
        </div>
    </div>

    <hr>

    <h5>Productos de la receta</h5>
    <div class="row mb-3">
        <div class="col-md-1">
            <label>Cantidad</label>
            <input type="number" id="cantidadInput" class="form-control">
        </div>

        <div class="col-md-3">
            <label>Producto</label>
            <select id="productoSelect" class="form-select">
                <option value="">-- Seleccionar producto --</option>
            </select>
        </div>

        <div class="col-md-2">
            <label>Fecha</label>
            <input type="date" id="fechaProductoInput" class="form-control">
        </div>

        <div class="col-md-2">
            <label>Cant. Recetas</label>
            <input type="number" id="recetasInput" class="form-control" min="1" step="1">
        </div>


        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" id="btnAgregarProducto">Agregar</button>
        </div>
        <div class="col-md-2 d-flex align-items-end mt-1">
            <button class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#productoModal">Buscar
                Producto</button>
        </div>
        <!-- Botón Modal -->

    </div>
    <div class="table-responsive">
        <table class="table table-bordered" id="tablaProductos">
            <thead>
                <tr>
                    <th>Cantidad</th>
                    <th>Producto</th>
                    <th>Fecha</th>
                    <th>Cant. Recetas</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>



    <!-- Modal -->
    <div class="modal fade" id="productoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Producto</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <!-- Mensaje de aviso -->
                        <div id="mensajeSeleccionTipo" class="text-center text-muted my-3">
                            Seleccione un tipo de receta para cargar los productos.
                        </div>

                        <!-- Tabla de productos -->
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
                            <tbody>
                                <!-- Productos cargados dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4 text-end">
        <button class="btn btn-success" id="btnGenerarReceta">Generar Receta</button>
    </div>

@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // Cargar técnicos
            $.get('/tecnico', function(data) {
                let select = $('#tecnicoSelect');
                select.append('<option value="">Seleccione</option>');
                data.forEach(t => {
                    select.append(
                        `<option value="${t.tecnico_id}">${t.tecnico_nombre} ${t.tecnico_apellido}</option>`
                    );
                });
            });

            // Cargar almacenes
            $.get('/almacen', function(data) {
                let select = $('#almacenSelect');
                select.append('<option value="">Seleccione</option>');
                data.forEach(a => {
                    select.append(`<option value="${a.almacen_id}">${a.almacen_nombre}</option>`);
                });
            });

            // Cargar productos según tipo
            // Cargar productos según tipo y reiniciar DataTable
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
                            `<option value="${p.producto_id}" data-nombre="${p.nombre}">${p.nombre}</option>`
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


            // Seleccionar desde modal
            $(document).on('click', '.seleccionar-modal', function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                $('#productoSelect').val(id);
            });

            // Agregar producto a tabla
            $('#btnAgregarProducto').on('click', function(e) {
                e.preventDefault();

                const cantidad = $('#cantidadInput').val();
                const productoId = $('#productoSelect').val();
                const productoNombre = $('#productoSelect option:selected').text();
                const fecha = $('#fechaProductoInput').val();
                const recetas = $('#recetasInput').val();

                if (!cantidad || !productoId || !fecha || !recetas) {
                    alert('Completa todos los campos');
                    return;
                }

                $('#tablaProductos tbody').append(`
<tr data-producto-id="${productoId}" data-cantidad="${cantidad}" data-fecha="${fecha}" data-recetas="${recetas}">
    <td>${cantidad}</td>
    <td>${productoNombre}</td>
    <td>${fecha}</td>
    <td>${recetas}</td>
    <td><button class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">Eliminar</button></td>
</tr>
`);


                // Limpiar campos
                $('#cantidadInput').val('');
                $('#productoSelect').val('');
                $('#fechaProductoInput').val('');
                $('#recetasInput').val('');
            });

            function validarCamposCantidadRecetas() {
                const cantidad = parseFloat($('#cantidadInput').val());
                const recetas = parseFloat($('#recetasInput').val());

                // Solo validar si ambos tienen valor numérico
                if (!isNaN(cantidad) && !isNaN(recetas)) {
                    if (recetas > cantidad) {
                        alert('La cantidad de recetas no puede ser mayor a la cantidad de producto.');
                        $('#recetasInput').val('');
                    } else if (cantidad < recetas) {
                        alert('La cantidad de producto no puede ser menor que la cantidad de recetas.');
                        $('#cantidadInput').val('');
                    }
                }
            }

            // Validar cuando se cambien los valores
            $('#cantidadInput, #recetasInput').on('input', validarCamposCantidadRecetas);
            // Generar receta
            $('#btnGenerarReceta').on('click', function() {
                const tipoReceta = $('#tipoRecetaSelect').val();
                const tecnico = $('#tecnicoSelect').val();
                const almacen = $('#almacenSelect').val();
                const fecha = $('#fechaLote').val();
                const productosAgregados = $('#tablaProductos tbody tr').length;

                if (!tipoReceta || !tecnico || !almacen || !fecha) {
                    alert('Completa todos los campos generales.');
                    return;
                }

                if (productosAgregados === 0) {
                    alert('Agrega al menos un producto a la receta.');
                    return;
                }

                // Aquí puedes enviar los datos con AJAX o redirigir a otro paso
                // Recolectar los productos desde la tabla
                const productos = [];
                $('#tablaProductos tbody tr').each(function() {
                    productos.push({
                        producto_id: $(this).data('producto-id'),
                        producto_cantidad: $(this).data('cantidad'),
                        fecha_emision: $(this).data('fecha'),
                        recetas: $(this).data('recetas')
                    });
                });

                const payload = {
                    tecnico_id: tecnico,
                    almacen_id: almacen,
                    receta_tipo: tipoReceta,
                    fecha_creacion: fecha,
                    productos: productos
                };

                $.ajax({
                    url: '/prescription', // Ajustá si tu ruta es diferente
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // Necesario si usás middleware CSRF
                    },
                    success: function(response) {
                        alert(response.message);
                        window.location.replace(
                            "{{ route('prescription.list_prescription') }}");
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            alert('Error: ' + xhr.responseJSON.error);
                        } else {
                            alert('Error inesperado.');
                        }
                    }
                });
            });

            function soloEnteros(selector) {
                $(selector).on('keydown', function(e) {
                    // Bloquear: punto, coma, letras, 'e', signos
                    if (
                        e.key === '.' ||
                        e.key === ',' ||
                        e.key === 'e' ||
                        e.key === '+' ||
                        e.key === '-' ||
                        e.key === 'E'
                    ) {
                        e.preventDefault();
                    }
                });

                $(selector).on('input', function() {
                    // Quitar todo lo que no sea dígito
                    this.value = this.value.replace(/[^\d]/g, '');
                });
            }

            // Aplicar a ambos campos
            soloEnteros('#recetasInput');
            soloEnteros('#cantidadInput');


        });
    </script>
@endsection
