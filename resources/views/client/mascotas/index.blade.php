@extends('layouts.app') {{-- Ajusta 'layouts.app' si tu plantilla base tiene otro nombre --}}

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Mis Mascotas</h2>
                    <a href="{{ route('client.mascotas.create') }}" class="btn btn-primary">
                        Registrar Nueva Mascota
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($mascotas->isEmpty())
                        <p>No tienes mascotas registradas aún. ¡Anímate a añadir una!</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Nombre</th>
                                        <th>Especie</th>
                                        <th>Raza</th>
                                        <th>Peso (kg)</th>
                                        <th>F. Nacimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mascotas as $mascota)
                                        <tr>
                                            <td>
                                                @if($mascota->hasMedia('avatars'))
                                                    <img src="{{ $mascota->getFirstMediaUrl('avatars', 'thumb') }}" alt="Avatar de {{ $mascota->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <img src="https://via.placeholder.com/50x50.png?text=Sin+Avatar" alt="Sin Avatar" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                @endif
                                            </td>
                                            <td>{{ $mascota->name }}</td>
                                            <td>{{ $mascota->species }}</td>
                                            <td>{{ $mascota->race ?? 'N/A' }}</td>
                                            <td>{{ $mascota->weight ?? 'N/A' }}</td>
                                            <td>{{ $mascota->birth_date ? \Carbon\Carbon::parse($mascota->birth_date)->format('d/m/Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('client.mascotas.edit', $mascota) }}" class="btn btn-sm btn-info">Editar</a>
                                                <form action="{{ route('client.mascotas.destroy', $mascota) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar a {{ $mascota->name }}?')">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection