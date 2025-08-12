@extends('layouts.app')

@section('title', 'Dashboard')


@section('content')
    <h1>Bienvenido, {{ auth()->user()->name }}</h1>
    <p>Este es el dashboard accesible para todos los roles.</p>
@endsection