@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')

        <h2>Usuarios</h2>

        @php
            $estadoFiltro = request('estado');
            if ($estadoFiltro === null || $estadoFiltro === '') {
                $estadoFiltro = '1'; // Por defecto activo
            }
        @endphp

        <div class="mb-3 d-flex align-items-center gap-3">
            <label for="filterEstado" class="form-label mb-0">Estado:</label>
            <select id="filterEstado" class="form-select" style="width: 150px;">
                <option value="all" {{ $estado === 'all' ? 'selected' : '' }}>Todos</option>
                <option value="1" {{ $estado == '1' ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ $estado == '0' ? 'selected' : '' }}>Inactivo</option>
            </select>


            <button class="btn btn-success ms-auto" id="btnNewUser">Nuevo Usuario</button>
        </div>

        <table id="usersTable" class="display table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Usuario</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr data-id="{{ $user->id }}">
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->lastname }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role ? $user->role->name : '-' }}</td>
                        <td>
                            @if ($user->estado)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm btn-edit" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <button class="btn btn-warning btn-sm btn-change-password" title="Cambiar contraseña"
                                data-id="{{ $user->id }}">
                                <i class="bi bi-key"></i>
                            </button>

                            <form method="POST" action="{{ route('users.changeEstado', $user->id) }}"
                                style="display:inline-block" class="form-change-estado">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="estado" value="{{ request('estado') }}">
                                <button type="submit"
                                    class="btn btn-sm {{ $user->estado ? 'btn-danger' : 'btn-success' }}"
                                    onclick="return confirm('¿Está seguro?')"
                                    title="{{ $user->estado ? 'Desactivar' : 'Restaurar' }}">
                                    @if ($user->estado)
                                        <i class="bi bi-person-dash"></i>
                                    @else
                                        <i class="bi bi-person-check"></i>
                                    @endif
                                </button>
                            </form>
                        </td>


                    </tr>
                @endforeach
            </tbody>
        </table>


    <!-- Modal Crear/Editar Usuario -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="userForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="userId" name="userId">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="name" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="lastname" class="form-label">Apellido *</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="username" class="form-label">Usuario *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Rol *</label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">Seleccione un rol</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6" id="passwordContainer">
                                <label for="password" class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6" id="passwordConfirmContainer">
                                <label for="password_confirmation" class="form-label">Confirmar contraseña *</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation">
                                <div class="invalid-feedback"></div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="btnSave">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="passwordForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="passwordModalLabel">Cambiar Contraseña</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="passwordUserId" name="userId">

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="new_password" name="password" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirmar Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="new_password_confirmation"
                                name="password_confirmation" required>
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-warning">Actualizar Contraseña</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Fin Modal Cambiar Contraseña -->
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            let userModal = new bootstrap.Modal(document.getElementById('userModal'));
            let isEdit = false;

            // Inicializar DataTable
            let table = $('#usersTable').DataTable({
                "order": [
                    [0, "asc"]
                ],
                // Puedes agregar más opciones si quieres
            });

            // Filtrar por estado
            $('#filterEstado').on('change', function() {
                let estado = $(this).val();
                // Recargar la página con filtro (también puedes implementar vía AJAX si quieres)
                let url = new URL(window.location.href);
                if (estado) {
                    url.searchParams.set('estado', estado);
                } else {
                    url.searchParams.delete('estado');
                }
                window.location.href = url.toString();
            });

            // Abrir modal para nuevo usuario
            $('#btnNewUser').click(function() {
                isEdit = false;
                $('#userModalLabel').text('Nuevo Usuario');
                $('#userForm')[0].reset();
                $('#userId').val('');
                clearValidationErrors();
                showPasswordFields(true);
                userModal.show();
            });

            // Abrir modal para editar usuario
            $('#usersTable').on('click', '.btn-edit', function() {
                isEdit = true;
                clearValidationErrors();
                let tr = $(this).closest('tr');
                let userId = tr.data('id');
                $('#userModalLabel').text('Editar Usuario');
                $('#userForm')[0].reset();
                $('#userId').val(userId);

                // Obtener datos del usuario vía AJAX
                $.get(`/users/${userId}/edit`, function(data) {
                    $('#name').val(data.name);
                    $('#lastname').val(data.lastname);
                    $('#username').val(data.username);
                    $('#phone').val(data.phone);
                    $('#email').val(data.email);
                    $('#role_id').val(data.role_id);
                    showPasswordFields(false);
                    userModal.show();
                });
            });

            // Mostrar/Ocultar campos contraseña según modo (crear o editar)
            function showPasswordFields(show) {
                if (show) {
                    $('#passwordContainer').show();
                    $('#passwordConfirmContainer').show();
                    $('#password').prop('required', true);
                    $('#password_confirmation').prop('required', true);
                } else {
                    $('#passwordContainer').hide();
                    $('#passwordConfirmContainer').hide();
                    $('#password').prop('required', false);
                    $('#password_confirmation').prop('required', false);
                }
            }

            // Limpiar errores de validación
            function clearValidationErrors() {
                $('#userForm').find('.is-invalid').removeClass('is-invalid');
                $('#userForm').find('.invalid-feedback').text('');
            }

            // Enviar formulario (Crear o Actualizar)
            $('#userForm').submit(function(e) {
                e.preventDefault();
                clearValidationErrors();

                let userId = $('#userId').val();
                let url = isEdit ? `/users/${userId}` : '/users';
                let method = isEdit ? 'PATCH' : 'POST';

                let formData = {
                    name: $('#name').val(),
                    lastname: $('#lastname').val(),
                    username: $('#username').val(),
                    phone: $('#phone').val(),
                    email: $('#email').val(),
                    role_id: $('#role_id').val(),
                    password: $('#password').val(),
                    password_confirmation: $('#password_confirmation').val(),
                };

                // Para PATCH Laravel espera _method en datos form
                if (method === 'PATCH') {
                    formData._method = 'PATCH';
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(res) {
                        userModal.hide();

                        let user = res.user;
                         let estadoActual = $('#filterEstado').val() || '';

                        // Construir la fila como array o HTML según tu tabla
                        let estadoBadge = user.estado ?
                            '<span class="badge bg-success">Activo</span>' :
                            '<span class="badge bg-secondary">Inactivo</span>';

                        // Obtén el token CSRF en tu JS antes, por ejemplo:
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content');

                        let acciones = `
    <td>
        <button class="btn btn-primary btn-sm btn-edit" title="Editar">
            <i class="bi bi-pencil-square"></i>
        </button>

        <button class="btn btn-warning btn-sm btn-change-password" title="Cambiar contraseña" data-id="${user.id}">
            <i class="bi bi-key"></i>
        </button>

        <form method="POST" action="/users/${user.id}/changeEstado" style="display:inline-block" class="form-change-estado">
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="estado" value="${estadoActual}">
            <button type="submit" class="btn btn-sm ${user.estado ? 'btn-danger' : 'btn-success'}" onclick="return confirm('¿Está seguro?')" title="${user.estado ? 'Desactivar' : 'Restaurar'}">
                ${user.estado ? '<i class="bi bi-person-dash"></i>' : '<i class="bi bi-person-check"></i>'}
            </button>
        </form>
    </td>
`;


                        // Si estamos editando (isEdit === true)
                        if (isEdit) {
                            // Buscar fila en DataTable
                            let row = table.row($(`tr[data-id="${user.id}"]`));
                            row.data([
                                user.id,
                                user.name,
                                user.lastname,
                                user.username,
                                user.phone || '-',
                                user.email,
                                user.role ? user.role.name : '-',
                                estadoBadge,
                                acciones
                            ]).draw(false);
                        } else {
                            // Crear nueva fila
                            let newRow = table.row.add([
                                user.id,
                                user.name,
                                user.lastname,
                                user.username,
                                user.phone || '-',
                                user.email,
                                user.role ? user.role.name : '-',
                                estadoBadge,
                                acciones
                            ]).draw(false).node();

                            // Agregar atributo data-id para futuras ediciones
                            $(newRow).attr('data-id', user.id);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Validación
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

            let passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));

            // Abrir modal cambiar contraseña
            $('#usersTable').on('click', '.btn-change-password', function() {
                clearPasswordValidationErrors();
                let userId = $(this).data('id');
                $('#passwordUserId').val(userId);
                $('#new_password').val('');
                $('#new_password_confirmation').val('');
                passwordModal.show();
            });

            // Limpiar errores de validación del password modal
            function clearPasswordValidationErrors() {
                $('#passwordForm').find('.is-invalid').removeClass('is-invalid');
                $('#passwordForm').find('.invalid-feedback').text('');
            }

            // Enviar formulario cambiar contraseña
            $('#passwordForm').submit(function(e) {
                e.preventDefault();
                clearPasswordValidationErrors();

                let userId = $('#passwordUserId').val();
                let formData = {
                    password: $('#new_password').val(),
                    password_confirmation: $('#new_password_confirmation').val(),
                    _method: 'PATCH' // Para Laravel
                };

                $.ajax({
                    url: `/users/${userId}/password`,
                    method: 'PATCH',
                    data: formData,
                    success: function(res) {
                        passwordModal.hide();
                        alert('Contraseña actualizada correctamente');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            if (errors.password) {
                                $('#new_password').addClass('is-invalid');
                                $('#new_password').next('.invalid-feedback').text(errors
                                    .password[0]);
                            }
                            if (errors.password_confirmation) {
                                $('#new_password_confirmation').addClass('is-invalid');
                                $('#new_password_confirmation').next('.invalid-feedback').text(
                                    errors.password_confirmation[0]);
                            }
                        } else {
                            alert(xhr.responseJSON.message)
                            alert('Error en el servidor al actualizar la contraseña');
                        }
                    }
                });
            });

        });
    </script>
@endsection
