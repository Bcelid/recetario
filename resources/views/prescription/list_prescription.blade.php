@extends('layouts.app')

@section('title', 'Lotes de Recetas')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <h2>Lotes de Recetas</h2>

    <div class="mb-3 d-flex align-items-center gap-3">
        <label class="mb-0">Estado:</label>
        <select id="filterEstado" class="form-select" style="width: 150px;">
            <option value="all">Todos</option>
            <option value="1" selected>Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <label class="mb-0">Técnico:</label>
        <select id="filterTecnico" class="form-select" style="width: 200px;"></select>

        <label class="mb-0">Almacén:</label>
        <select id="filterAlmacen" class="form-select" style="width: 200px;"></select>
    </div>


    <div class="table-responsive">
        <table id="lotesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Lote</th>
                    <th>Fecha</th>
                    <th>Firmado</th>
                    <th>Enviado</th>
                    <th>Almacén</th>
                    <th>Técnico</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modal Enviar Correo -->
    <div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-labelledby="modalEnviarCorreoLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <form id="formEnviarCorreo">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Enviar Receta por Correo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="correoLoteId">
                        <input type="hidden" id="correoAlmacenId">
                        <div class="mb-3">
                            <label for="correoAsunto" class="form-label">Asunto</label>
                            <input type="text" class="form-control" id="correoAsunto" value="ENVÍO DE RECETA">
                        </div>

                        <div class="mb-3">
                            <label for="remitente" class="form-label">Remitente</label>
                            <input type="text" class="form-control" id="remitente" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="destinatario" class="form-label">Destinatario</label>
                            <input type="email" class="form-control" id="destinatario" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="cc" class="form-label">CC</label>
                            <input type="email" class="form-control" id="cc">
                        </div>
                        <div class="mb-3">
                            <label for="cuerpoCorreo" class="form-label">Mensaje</label>
                            <textarea class="form-control" id="cuerpoCorreo" rows="6"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Documento adjunto:</label>
                            <div id="listaArchivos"></div>
                        </div>
                        <div class="mb-3 mt-4">
                            <h6>Historial de envíos</h6>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Destinatario</th>
                                    </tr>
                                </thead>
                                <tbody id="historialEnviosBody">
                                    <!-- Aquí se cargarán las filas -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="btn-enviar-correo" class="btn btn-primary">Enviar Correo</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ver Recetas del Lote -->
    <div class="modal fade" id="modalVerRecetas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Lote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    
                    <div class="mb-3">
                        <strong>Lote:</strong> <span id="verLoteNumero"></span><br>
                        <strong>Tipo:</strong> <span id="verLoteTipo"></span>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaRecetasLote">
                            <!-- Se llena dinámicamente -->
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            // Cargar técnicos y almacenes primero, luego inicializar la tabla
            let tecnicoListo = false;
            let almacenListo = false;

            function verificarCarga() {
                if (tecnicoListo && almacenListo) {
                    inicializarDataTable();
                }
            }

            // Cargar técnicos
            $.get("{{ route('tecnico.index') }}", {
                estado: '1'
            }, function(data) {
                $('#filterTecnico').append(`<option value="all" selected>Todos</option>`);
                data.forEach(function(tecnico) {
                    $('#filterTecnico').append(
                        `<option value="${tecnico.tecnico_id}">${tecnico.tecnico_nombre} ${tecnico.tecnico_apellido}</option>`
                    );
                });
                tecnicoListo = true;
                verificarCarga();
            });

            // Cargar almacenes
            $.get("{{ route('almacen.index') }}", {
                estado: '1'
            }, function(data) {
                $('#filterAlmacen').append(`<option value="all" selected>Todos</option>`);
                data.forEach(function(almacen) {
                    $('#filterAlmacen').append(
                        `<option value="${almacen.almacen_id}">${almacen.almacen_nombre}</option>`
                    );
                });
                almacenListo = true;
                verificarCarga();
            });

            // Declarar tabla para acceder globalmente
            let table;

            function inicializarDataTable() {
                table = $('#lotesTable').DataTable({
                    ajax: {
                        url: "{{ route('receta.lotes.data') }}",
                        dataSrc: '',
                        data: function(d) {
                            d.estado = $('#filterEstado').val();
                            d.tecnico_id = $('#filterTecnico').val();
                            d.almacen_id = $('#filterAlmacen').val();
                        }
                    },
                    columns: [{
                            data: 'receta_tipo',
                            render: function(tipo) {
                                return tipo == 0 ?
                                    '<span class="badge bg-success">Agrícola</span>' :
                                    '<span class="badge bg-primary">Veterinario</span>';
                            }
                        },
                        {
                            data: 'receta_lote_id'
                        },
                        {
                            data: 'fecha_creacion'
                        },
                        {
                            data: 'receta_lote_firmado',
                            render: function(firmado) {
                                return firmado ?
                                    '<span class="badge bg-success">Sí</span>' :
                                    '<span class="badge bg-secondary">No</span>';
                            }
                        },
                        {
                            data: 'receta_lote_fecha_envio',
                            render: function(fecha) {
                                return fecha ? fecha : 'No enviado';
                            }
                        },
                        {
                            data: 'almacen_nombre'
                        },
                        {
                            data: 'tecnico_nombre'
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data) {
                                const isActivo = data.receta_lote_estado == 1;
                                const isFirmado = data.receta_lote_firmado;

                                return `
            <div class="d-flex gap-1">
                 <a href="javascript:void(0)" 
                    class="btn btn-sm btn-info btn-ver-recetas" 
                    data-id="${data.receta_lote_id}" 
                    data-tipo="${data.receta_tipo}"
                    data-lote="${data.receta_lote_id}"
                    title="Ver recetas del lote">
                    <i class="fa-solid fa-eye"></i>
                    </a>
                <button type="button" class="btn btn-sm btn-warning btn-firmar" title="Firmar" data-id="${data.receta_lote_id}" ${!isActivo || isFirmado ? 'disabled' : ''}>
    <i class="fa-solid fa-pen-nib"></i>
</button>

                <button class="btn btn-sm btn-primary btn-enviar-correo"  title="Enviar" data-id="${data.receta_lote_id}" ${!isActivo || !isFirmado ? 'disabled' : ''}>
    <i class="fa-solid fa-paper-plane"></i>
</button>

                <button class="btn btn-sm ${isActivo ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${data.receta_lote_id}">
                    ${isActivo ? '<i class="fa-solid fa-xmark-circle"></i>' : '<i class="fa-solid fa-check-circle"></i>'}
                </button>
            </div>
        `;
                            }
                        }

                    ]
                });

                // Filtros
                $('#filterEstado, #filterTecnico, #filterAlmacen').on('change', function() {
                    table.ajax.reload();
                });
            }

            // Cambiar estado del lote
            $('#lotesTable').on('click', '.btn-toggle-estado', function() {

                if (!confirm('¿Estás seguro de cambiar el estado del lote?')) return;

                let $btn = $(this);
                let id = $btn.data('id');
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span>');




                $.ajax({
                    url: `/receta/${id}`,
                    method: 'DELETE',
                    success: function(res) {
                        //alert(res.message);
                        table.ajax.reload(null,
                            false); // Recargar tabla sin reiniciar paginación
                    },
                    error: function() {
                        alert('Error al cambiar el estado del lote.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Firmar lote
            $('#lotesTable').on('click', '.btn-firmar', function(e) {
                e.preventDefault();

                const id = $(this).data('id');
                console.log('[Firmar Lote] Click detectado en lote ID:', id);

                if (!confirm('¿Deseas firmar este lote? Esta acción no se puede deshacer.')) {
                    console.log('[Firmar Lote] Confirmación cancelada por el usuario.');
                    return;
                }

                console.log('[Firmar Lote] Confirmación aceptada, enviando petición AJAX...');

                $.ajax({
                    url: `/receta/firmar`,
                    method: 'POST',
                    data: {
                        id: id
                    },
                    success: function(res) {
                        console.log('[Firmar Lote] Respuesta exitosa del servidor:', res);

                        // Mostrar alerta de caducidad si existe
                        if (res.alerta_caducidad) {
                            alert('⚠️ ADVERTENCIA: ' + res.alerta_caducidad.mensaje +
                                '\n\nAcción recomendada: ' + res.alerta_caducidad
                                .accion_recomendada);
                        }

                        alert(res.message || 'Lote firmado correctamente.');
                        table.ajax.reload(null, false); // Recargar sin reiniciar paginación
                    },
                    error: function(xhr) {
                        console.error('[Firmar Lote] Error en la petición AJAX:', xhr);

                        let msg = 'Error al firmar el lote.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;

                            // Mensaje específico para firma caducada
                            if (xhr.responseJSON.error === 'FIRMA CADUCADA') {
                                msg = '❌ FIRMA CADUCADA\n' +
                                    xhr.responseJSON.message +
                                    '\n\nPor favor, actualice la firma digital para continuar.';
                            }
                        }

                        alert(msg);
                    }
                });
            });

            // Abrir modal de correo
            $('#lotesTable').on('click', '.btn-enviar-correo', function() {
                const id = $(this).data('id');

                console.log('[Correo] Cargando datos del lote:', id);

                $.get(`/receta/email/datos/${id}`, function(res) {
                    console.log('[Correo] Datos recibidos:', res);

                    $('#correoLoteId').val(res.lote_id);
                    $('#correoAlmacenId').val(res.almacen_id);
                    $('#remitente').val(`${res.remitente_nombre} <${res.remitente_correo}>`);
                    $('#destinatario').val(res.destinatario);
                    $('#cuerpoCorreo').val(res.body);

                    // ---------------------------------------------
                    // MOSTRAR LISTA DE ARCHIVOS INDIVIDUALES
                    // ---------------------------------------------
                    let html = "";
                    res.documentos.forEach((doc, index) => {
                        html += `
                            <div class="archivo-item mb-2 d-flex align-items-center" data-index="${index}">
                                <a href="${doc.url}" target="_blank">${doc.nombre}</a>
                                <button type="button" class="btn btn-sm btn-danger ms-2 quitar-archivo" data-index="${index}">
                                    Quitar
                                </button>
                                <input type="hidden" name="paths[]" value="${doc.path}">
                            </div>
                        `;
                    });

                    $('#listaArchivos').html(html);


                    // ---------------------------------------------
                    //  BOTÓN QUITAR (delegado)
                    // ---------------------------------------------
                    $('#listaArchivos').off('click').on('click', '.quitar-archivo', function() {
                        const index = $(this).data('index');
                        $(`[data-index="${index}"]`).remove();
                    });


                    // ---------------------------------------------
                    // HISTORIAL (igual que antes)
                    // ---------------------------------------------
                    $('#historialEnviosBody').empty();

                    if (res.historial && res.historial.length > 0) {

                        res.historial.forEach(envio => {
                            // envio = { fecha, destinatario, recetas: [ {nombre}, {nombre} ] }

                            let listaRecetas = "";

                            if (envio.recetas && envio.recetas.length > 0) {
                                listaRecetas = envio.recetas
                                    .map(r => r.nombre)
                                    .join("<br>");
                            } else {
                                listaRecetas = "—";
                            }

                            $('#historialEnviosBody').append(`
                                <tr>
                                    <td>${envio.fecha}</td>
                                    <td>${envio.destinatario}</td>
                                    <td>${listaRecetas}</td>
                                </tr>
                            `);
                        });

                    } else {
                        $('#historialEnviosBody').append(`
                            <tr>
                                <td colspan="3" class="text-center">No hay envíos previos.</td>
                            </tr>
                        `);
                    }

                    // ---------------------------------------------
                    // MOSTRAR MODAL
                    // ---------------------------------------------
                    const modal = new bootstrap.Modal(document.getElementById('modalEnviarCorreo'));
                    modal.show();

                }).fail(function(xhr) {
                    let msg = 'Error al obtener los datos del correo.';
                    if (xhr.responseJSON?.error) {
                        msg = xhr.responseJSON.error;
                    }
                    alert(msg);
                });
            });


            $('#formEnviarCorreo').on('submit', function(e) {
                e.preventDefault();

                let $btn = $('#btn-enviar-correo');
                let original = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...'
                );

                let formData = new FormData(this);

                formData.append("receta_lote_id", $('#correoLoteId').val());
                formData.append("almacen_id", $('#correoAlmacenId').val());
                formData.append("to", $('#destinatario').val());
                formData.append("cc", $('#cc').val());
                formData.append("subject", $('#correoAsunto').val());
                formData.append("body", $('#cuerpoCorreo').val());

                $.ajax({
                    url: '/receta/enviar-correo',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        $btn.html('<i class="fa fa-check text-white"></i> Enviado');
                        $btn.prop('disabled', false).html(original);
                        $('#modalEnviarCorreo').modal('hide');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        let msg = xhr.responseJSON?.error ?? 'Error al enviar el correo.';
                        alert(msg);
                        $btn.prop('disabled', false).html(original);
                    }
                });
            });




            // Abrir modal para ver recetas del lote
            // Abrir modal para ver recetas del lote
            $('#lotesTable').on('click', '.btn-ver-recetas', function () {

                const loteId = $(this).data('id');
                const loteNumero = $(this).data('lote');
                const tipo = $(this).data('tipo') == 0 ? 'Agrícolas' : 'Veterinarias';

                // Datos del encabezado del modal
                $('#verLoteNumero').text(loteNumero);
                $('#verLoteTipo').text(tipo);

                // Limpiar tabla
                $('#tablaRecetasLote').html('<tr><td colspan="3" class="text-center">Cargando...</td></tr>');

                // Obtener recetas del lote
                $.get(`/receta/lote/${loteId}/recetas`, function(res) {

                    if (res.recetas.length === 0) {
                        $('#tablaRecetasLote').html(`
                            <tr>
                                <td colspan="3" class="text-center">No hay recetas en este lote.</td>
                            </tr>
                        `);
                        return;
                    }

                    let html = "";

                    res.recetas.forEach(rec => {
                        html += `
                            <tr>
                                <td>${rec.receta_numero}</td>
                                <td>${rec.fecha_emision}</td>
                                <td>
                                    <a href="/storage/${rec.path}?v=${Date.now()}" target="_blank" class="btn btn-sm btn-primary">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        `;
                    });

                    $('#tablaRecetasLote').html(html);

                }).fail(() => {
                    $('#tablaRecetasLote').html(`
                        <tr><td colspan="3" class="text-center text-danger">Error al cargar recetas.</td></tr>
                    `);
                });

                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('modalVerRecetas'));
                modal.show();
            });
        });
    </script>
@endsection
