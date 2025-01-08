@php
    use Illuminate\Support\Facades\Auth;
@endphp
@extends('layouts.admin')

    <!DOCTYPE html>
<html lang="es">
<head>
    <title>Panel de Administrador</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" href="{{ asset('images.png') }}">
</head>
<body>
<style>
    nav * a {
        text-decoration: none;
    }
</style>

<!-- Navbar -->
<nav class="navbar navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand text-danger h1" href="{{ route('home') }}">Change.org</a>
        <div>
            @if(Auth::check())
                <a href="{{ route('logout') }}"
                   class="link-danger"
                   onclick="event.preventDefault();document.getElementById('logout').submit();">Cerrar sesión</a>
                <form id="logout" method="POST" action="{{ route('logout') }}">
                    @csrf
                </form>
            @endif
        </div>
    </div>
</nav>

<!-- Contenido -->
<div class="container my-4">
    <h3>Panel de Administrador</h3>
    <a href="{{ route('peticiones.create') }}" class="btn btn-primary mb-3">Nueva Petición</a>
    <table class="table table-bordered table-hover text-center">
        <thead class="table-light">
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Título</th>
            <th>Descripción</th>
            <th>Firmantes</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        @forelse($peticiones as $index => $peticion)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $peticion->id }}</td>
                <td>{{ $peticion->titulo }}</td>
                <td>{{ $peticion->descripcion }}</td>
                <td>{{ $peticion->firmantes }}</td>
                <td>
                    @if($peticion->estado == 'pendiente')
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    @else
                        <span class="badge bg-success">Aceptada</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('peticiones.edit', $peticion->id) }}" class="btn btn-success btn-sm">✏️</a>
                    <a href="{{ route('peticiones.show', $peticion->id) }}" class="btn btn-info btn-sm">🔍</a>
                    <form action="{{ route('peticiones.destroy', $peticion->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">❌</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No hay peticiones disponibles.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
