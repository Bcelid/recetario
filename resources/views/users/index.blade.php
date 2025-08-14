@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')

    <h2>Usuarios</h2>

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
        <tbody></tbody>
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
                                <input type="email" class="form-control" id="email" name="email" required
                                    autocomplete="username">

                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Rol *</label>
                                <select class="form-select" id="role_id" name="role_id" required>

                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6" id="passwordContainer">
                                <label for="password" class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    autocomplete="new-password">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6" id="passwordConfirmContainer">
                                <label for="password_confirmation" class="form-label">Confirmar contraseña *</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" autocomplete="new-password">
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
                            <input type="password" class="form-control" id="new_password" name="password" required
                                autocomplete="new-password">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirmar Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="new_password_confirmation"
                                name="password_confirmation" required autocomplete="new-password">
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
            loadRoles();

            function loadRoles() {
                $.get('/users/roles/active', function(roles) {
                    $('#role_id').empty().append('<option value="">Seleccione un rol</option>');
                    roles.forEach(role => {
                        $('#role_id').append(`<option value="${role.id}">${role.name}</option>`);
                    });
                });
            }

            function clearUserValidationErrors() {
                $('#userForm').find('.is-invalid').removeClass('is-invalid');
                $('#userForm').find('.invalid-feedback').text('');
            }



            // Inicializar DataTable
            let table = $('#usersTable').DataTable({
                ajax: {
                    url: '/users', // Ruta al método index
                    dataSrc: '',
                    data: function(d) {
                        d.estado = $('#filterEstado').val() || 'all';
                    }
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'lastname'
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'role.name',
                        defaultContent: 'Sin rol'
                    },
                    {
                        data: 'estado',
                        render: estado => estado == 1 ?
                            '<span class="badge bg-success">Activo</span>' :
                            '<span class="badge bg-secondary">Inactivo</span>'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(user) {
                            return `
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-primary btn-edit" data-id="${user.id}">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="btn btn-sm btn-warning btn-password" data-id="${user.id}">
                    <i class="fa-solid fa-key"></i>
                </button>
                <button class="btn btn-sm ${user.estado == "1" ? 'btn-danger' : 'btn-success'} btn-toggle-estado" data-id="${user.id}">
                    ${user.estado == "1" 
                        ? '<i class="fa-solid fa-user-minus"></i>' 
                        : '<i class="fa-solid fa-user-check"></i>'}
                </button>
            </div>
        `;
                        }
                    }

                ]
            });

            // Filtro de estado (si tienes un select)
            $('#filterEstado').on('change', function() {
                table.ajax.reload();
            });

            // Eventos para los botones
            $('#usersTable').on('click', '.btn-edit', function() {
                const userId = $(this).data('id');
                editUser(userId);
            });

            $('#usersTable').on('click', '.btn-password', function() {
                const userId = $(this).data('id');
                $('#passwordUserId').val(userId);
                $('#passwordModal').modal('show');
            });

            $('#usersTable').on('click', '.btn-toggle-estado', function() {
                const userId = $(this).data('id');
                toggleEstado(userId);
            });

            function toggleEstado(userId) {
                if (!confirm("¿Estás seguro de que deseas cambiar el estado del usuario?")) {
                    return;
                }

                $.ajax({
                    url: `/users/${userId}/changeEstado`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        $('#usersTable').DataTable().ajax.reload();
                    },
                    error: function() {
                        alert('Ocurrió un error al cambiar el estado del usuario.');
                    }
                });
            }



            $('#btnNewUser').on('click', function() {
                isEdit = false;

                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#userModalLabel').text('Nuevo Usuario');

                $('#passwordContainer').show();
                $('#passwordConfirmContainer').show();
                userModal.show();
            });

            function editUser(userId) {
                $.get(`/users/${userId}`, function(user) {
                    $('#userId').val(user.id);
                    $('#name').val(user.name);
                    $('#lastname').val(user.lastname);
                    $('#username').val(user.username);
                    $('#phone').val(user.phone);
                    $('#email').val(user.email);
                    $('#role_id').val(user.role_id);

                    // Ocultar campos de contraseña
                    $('#passwordContainer').hide();
                    $('#passwordConfirmContainer').hide();

                    $('#userModalLabel').text('Editar Usuario');
                    $('#userModal').modal('show');
                });
            }

            $('#userForm').submit(function(e) {
                e.preventDefault();
                clearUserValidationErrors();
                let userId = $('#userId').val();
                let method = userId ? 'PATCH' : 'POST';
                let url = userId ? `/users/${userId}` : '/users';

                let formData = {
                    name: $('#name').val(),
                    lastname: $('#lastname').val(),
                    username: $('#username').val(),
                    phone: $('#phone').val(),
                    email: $('#email').val(),
                    role_id: $('#role_id').val(),
                    password: $('#password').val(),
                    password_confirmation: $('#password_confirmation').val(),
                    _method: method
                };

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#userModal').modal('hide');
                        $('#usersTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        // Manejador de errores de validación
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                let input = $(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.next('.invalid-feedback').text(errors[field][0]);
                            }
                        } else {
                            alert('Error en el servidor al guardar el usuario');
                        }
                    }
                });
            });



            let passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));



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
