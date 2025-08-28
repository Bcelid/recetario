@extends('layouts.app')

@section('title', 'Configuración de Correo')

@section('content')
    <div class="container d-flex justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4 text-center">Configuración de Correo Electrónico</h2>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('correo.config.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="smtp_provider" class="form-label">Proveedor *</label>
                    <select class="form-select @error('smtp_provider') is-invalid @enderror" name="smtp_provider"
                        id="smtp_provider" required>
                        <option value="gmail" {{ $config->smtp_provider === 'gmail' ? 'selected' : '' }}>Gmail</option>
                        <option value="outlook" {{ $config->smtp_provider === 'outlook' ? 'selected' : '' }}>Outlook
                        </option>
                    </select>
                    @error('smtp_provider')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="smtp_username" class="form-label">Correo del Usuario *</label>
                    <input type="email" class="form-control @error('smtp_username') is-invalid @enderror"
                        name="smtp_username" value="{{ old('smtp_username', $config->smtp_username) }}" required>
                    @error('smtp_username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="smtp_password" class="form-label">Contraseña de Aplicación *</label>
                    <input type="password" class="form-control @error('smtp_password') is-invalid @enderror"
                        name="smtp_password" autocomplete="current-password"
                        value="{{ old('smtp_password', $config->smtp_password) }}" required>
                    @error('smtp_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <div class="mb-3">
                    <label for="smtp_from_name" class="form-label">Nombre del Remitente *</label>
                    <input type="text" class="form-control @error('smtp_from_name') is-invalid @enderror"
                        name="smtp_from_name" value="{{ old('smtp_from_name', $config->smtp_from_name) }}" required>
                    @error('smtp_from_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="smtp_from_address" class="form-label">Correo del Remitente *</label>
                    <input type="email" class="form-control @error('smtp_from_address') is-invalid @enderror"
                        name="smtp_from_address" value="{{ old('smtp_from_address', $config->smtp_from_address) }}"
                        required>
                    @error('smtp_from_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-primary">Guardar Configuración</button>
                    <button type="button" class="btn btn-secondary" id="btnTestConnection">Probar Conexión</button>
                </div>

                <div id="testResult" class="mt-3"></div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('btnTestConnection').addEventListener('click', function() {
            $('#loadingModal').modal('show');
            const testResult = document.getElementById('testResult');
            testResult.innerHTML = ''; // limpiar mensaje previo

            fetch("{{ route('correo.config.test') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        smtp_provider: document.getElementById('smtp_provider').value,
                        smtp_username: document.querySelector('input[name="smtp_username"]').value,
                        smtp_password: document.querySelector('input[name="smtp_password"]').value,
                        smtp_from_name: document.querySelector('input[name="smtp_from_name"]').value,
                        smtp_from_address: document.querySelector('input[name="smtp_from_address"]')
                            .value,
                    })
                })
                .then(async response => {
                    $('#loadingModal').modal('hide');
                    const contentType = response.headers.get("content-type");
                    if (!response.ok) {
                        
                        if (contentType && contentType.includes("application/json")) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || "Error desconocido");
                        } else {
                            throw new Error("Error en la solicitud HTTP");
                        }
                    }
                    if (contentType && contentType.includes("application/json")) {
                        return response.json();
                    } else {
                        throw new Error("Respuesta no es JSON");
                    }
                })
                .then(data => {
                    $('#loadingModal').modal('hide');
                    let alertClass, message;

                    if (data.success) {
                        alertClass = 'alert-success';
                        message = data.message; // mensaje del backend
                    } else {
                        alertClass = 'alert-danger';
                        message = 'Error de conexión, revisar los datos ingresados.';
                        // Opcionalmente, puedes agregar el mensaje detallado: 
                        // message += ' Detalle: ' + data.message;
                    }

                    testResult.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
                })
                .catch(error => {
                    $('#loadingModal').modal('hide');
                    testResult.innerHTML =
                        `<div class="alert alert-danger">Error al probar conexión, revisar los datos ingresados</div>`;
                    console.error("Error:", error);
                });
        });
    </script>

@endsection
