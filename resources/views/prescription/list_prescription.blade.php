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
                            <label for="cuerpoCorreo" class="form-label">Mensaje</label>
                            <textarea class="form-control" id="cuerpoCorreo" rows="6"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Documento adjunto:</label>
                            <p><a href="#" id="linkDocumento" target="_blank">Ver documento</a></p>
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
                 <a href="/storage/${data.receta_lote_path}" class="btn btn-sm btn-info" title="Ver" target="_blank">
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
                        alert(res.message || 'Lote firmado correctamente.');
                        table.ajax.reload(null, false); // Recargar sin reiniciar paginación
                    },
                    error: function(xhr) {
                        console.error('[Firmar Lote] Error en la petición AJAX:', xhr);

                        let msg = 'Error al firmar el lote.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
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
                    $('#linkDocumento').attr('href', res.documento_url);

                    // Limpiar tabla
                    $('#historialEnviosBody').empty();

                    // Suponiendo que res.historial es un array con los envíos previos
                    if (res.historial && res.historial.length > 0) {
                        res.historial.forEach(envio => {
                            $('#historialEnviosBody').append(`
            <tr>
                <td>${envio.fecha}</td>
                <td>${envio.destinatario}</td>
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
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...');

                const data = {
                    receta_lote_id: $('#correoLoteId').val(),
                    almacen_id: $('#correoAlmacenId').val(),
                    to: $('#destinatario').val(),
                    subject: $('#correoAsunto').val(),
                    body: $('#cuerpoCorreo').val(),
                };


                $.post('/receta/enviar-correo', data, function(res) {
                    $btn.html('<i class="fa fa-check text-white"></i> Enviado');
                    $('#modalEnviarCorreo').modal('hide');
                    table.ajax.reload(null, false);
                }).fail(function(xhr) {
                    console.error('Respuesta completa:', xhr);
                    let msg = 'Error al enviar el correo.';
                    if (xhr.responseJSON?.error) {
                        msg = xhr.responseJSON.error;
                    }
                    alert(msg);
                });
            });


        });
    </script>
@endsection
