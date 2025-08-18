@extends('layouts.app')

@section('title', 'Detalle de Producto')

@section('content')
<div class="container">
    <h2>{{ $producto->producto_nombre }}</h2>
    <p><strong>Tipo:</strong> {{ $producto->producto_tipo == 0 ? 'Agrícola' : 'Veterinario' }}</p>
    <p><strong>Concentración:</strong> {{ $producto->producto_concentracion }}</p>
    <p><strong>Presentación:</strong> {{ ($producto->producto_presentacion . " " . ($producto->unidadMedida->unidad_medida_detalle ?? '')) ?: '-' }}</p>
    <p><strong>Formulación:</strong> {{ $producto->formulacion->formulacion_nombre ?? '-' }}</p>
    <p><strong>Diagnóstico:</strong> {{ $producto->producto_diagnostico ?? 'No especificado' }}</p>

    <h4 class="mt-4">Ingredientes Activos</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Ingrediente</th>
                <th>Cantidad</th>
                <th>Unidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($producto->ingredientes as $ing)
                <tr>
                    <td>{{ $ing->ingredienteActivo->ingrediente_activo_nombre }}</td>
                    <td>{{ $ing->cantidad }}</td>
                    <td>{{ $ing->unidadMedida->unidad_medida_detalle ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4 class="mt-4">Dosificación</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>{{ $producto->producto_tipo == 0 ? 'Cultivo' : 'Especie' }}</th>
                <th>{{ $producto->producto_tipo == 0 ? 'Maleza' : 'Subespecie' }}</th>
                @if($producto->producto_tipo == 0)
                    <th>Dosis</th>
                    <th>Unidad</th>
                @endif
                <th>Aplicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($producto->dosificaciones as $d)
                <tr>
                    <td>{{ $producto->producto_tipo == 0 ? $d->cultivo->cultivo_nombre : $d->subespecie->especie->especie_nombre }}</td>
                    <td>{{ $producto->producto_tipo == 0 ? $d->maleza->maleza_nombre : $d->subespecie->subespecie_nombre }}</td>
                    @if($producto->producto_tipo == 0)
                        <td>{{ $d->dosis }}</td>
                        <td>{{ $d->unidadMedidaDosificacion->unidad_medida_dosificacion_representacion ?? '-' }}</td>
                    @endif
                    <td>{{ $d->dosificacion_aplicacion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    
    <a href="{{ route('product.index') }}" class="btn btn-secondary">Volver</a>
</div>
@endsection
