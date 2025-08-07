<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recetario</title>

    <!--Tipo de letra-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- AdminLTE (opcional si lo quieres mantener) -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}" />

    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 100%;
            max-width: 500px;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <div class="card shadow">
            <div class="card-header text-center">
                <img src="{{ asset('img/Logocombinado_sinfondo.png') }}" alt="Recetario Logo" class="img-fluid"
                    style="max-height: 150px;">
            </div>
            <div class="card-body">
                <form action="{{ route('login') }}" method="POST" class="needs-validation" novalidate>
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-floating mb-3 position-relative">
                        <input type="text" name="username" value="{{ old('username') }}" class="form-control"
                            placeholder="Usuario" id="username" required>
                        <label for="username">Usuario</label>
                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="invalid-feedback">
                            Por favor ingresa tu usuario.
                        </div>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <input type="password" name="password" class="form-control" id="loginPassword"
                            placeholder="Contraseña" required>
                        <label for="loginPassword">Contraseña</label>
                        <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <div class="invalid-feedback">
                            Por favor ingresa tu contraseña.
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-portlogistics">Iniciar Sesión</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- AdminLTE-->
    <script src="{{ asset('js/adminlte.js') }}"></script>

    <!-- Validación de formulario -->
    <script>
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>

</html>
