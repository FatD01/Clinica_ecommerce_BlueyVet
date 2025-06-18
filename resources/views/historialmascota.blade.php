<!DOCTYPE html>
<html>
<head>
    <title>Historiales Médico de {{ $mascota->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('historialmascota.css') }}">
    <link rel="stylesheet" href="{{ asset('seccionesactivas.css') }}">
    
</head>
<body>
<div class="container mt-5">

<!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">BlueyVet</div>
      <nav>
    <ul>
        <li>
            <a href="{{ route('veterinarian.citas') }}"
               class="{{ request()->routeIs('veterinarian.citas') ? 'active' : '' }}">
               Consultar Citas
            </a>
        </li>
        <li>
  <a href="{{ route('historialmedico.index') }}"
     class="{{ request()->routeIs('historialmedico.*') || request()->routeIs('veterinarian.historial') ? 'active' : '' }}">
     Historial Médico
  </a>
</li>


        
        <li>
            <a href="{{ route('veterinarian.profile') }}"
   class="{{ request()->routeIs('veterinarian.profile*') || request()->routeIs('veterinarian.edit') ? 'active' : '' }}">
   Mi Información
</a>

        </li>
    </ul>
</nav>

      <div class="user">Hola, <strong>{{ Auth::user()->name }}</strong></div>
    </aside>

    {{-- Botón Atrás elegante --}}

    <div class="main-content container mt-4">
    <a href="javascript:history.back()" class="btn btn-outline-secondary mb-3">
        &#8592; Atrás
    </a>

    <h2 class="mb-4">Historial Médico de {{ $mascota->name }}</h2>

    {{-- Filtro por fecha --}}
    <form method="GET" action="{{ route('veterinarian.historial', ['mascota' => $mascota->id]) }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Desde</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-3">
            <label>Hasta</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </div>
    </form>

    {{-- Tarjeta única con contenido desplegable --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Historial Clínico</h5>
        </div>
        <div class="card-body">
            {{-- Datos del dueño (siempre visibles) --}}
            <h6>Datos del Dueño</h6>
            <p><strong>Nombre:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
            <p><strong>Email:</strong> {{ $usuario->email ?? 'No disponible' }}</p>
            <p><strong>Teléfono:</strong> {{ $cliente->telefono ?? 'No registrado' }}</p>
            <p><strong>Dirección:</strong> {{ $cliente->direccion ?? 'No registrada' }}</p>
        </div>

        {{-- Contenido desplegable --}}
        <div class="collapse" id="contenidoExpandible">
            <div class="card-body border-top">
                {{-- Datos de la mascota --}}
                <h6>Datos de la Mascota</h6>
                <div class="row align-items-center mb-3">
                    <div class="col-md-3">
                        @if($mascota->image)
                            <img src="{{ asset('storage/' . $mascota->image) }}" class="mascota-img" alt="Imagen de la mascota">
                        @else
                            <p><em>No hay imagen disponible.</em></p>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <p><strong>Nombre:</strong> {{ $mascota->name }}</p>
                        <p><strong>Especie:</strong> {{ $mascota->species }}</p>
                        <p><strong>Raza:</strong> {{ $mascota->race }}</p>
                        <p><strong>Peso:</strong> {{ $mascota->weight }} kg</p>
                        <p><strong>Nacimiento:</strong> {{ $mascota->birth_date }}</p>
                        <p><strong>Alergias:</strong> {{ $mascota->allergies }}</p>
                    </div>
                </div>

                {{-- Historial Médico --}}
                <h6>Consultas Médicas</h6>
                @forelse ($registros as $registro)
                    <div class="mb-3 p-3 border rounded bg-light">
                        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($registro->consultation_date)->format('d/m/Y') }}</p>
                        <p><strong>Razón de consulta:</strong> {{ $registro->reason_for_consultation ?? 'No registrada' }}</p>
                        <p><strong>Diagnóstico:</strong> {{ $registro->diagnosis ?? 'No registrado' }}</p>
                        <p><strong>Tratamiento:</strong> {{ $registro->treatment ?? 'No registrado' }}</p>
                        <p><strong>Notas:</strong> {{ $registro->notes ?? 'Sin notas' }}</p>
                        <p><strong>Prescripción:</strong> {{ $registro->prescription ?? 'No registrada' }}</p>
                        <p><strong>Observaciones:</strong> {{ $registro->observations ?? 'Sin observaciones' }}</p>
                    </div>
                @empty
                    <div class="alert alert-info">No hay registros médicos disponibles para esta mascota.</div>
                @endforelse
            </div>
        </div>

        {{-- Botón para desplegar o contraer --}}
        <div class="toggle-button" data-bs-toggle="collapse" data-bs-target="#contenidoExpandible" aria-expanded="false" aria-controls="contenidoExpandible">
            <span id="toggleIcon">&#x25BC; Ver más</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toggle = document.querySelector('[data-bs-toggle="collapse"]');
    const icon = document.getElementById('toggleIcon');
    const contenido = document.getElementById('contenidoExpandible');

    contenido.addEventListener('shown.bs.collapse', () => {
        icon.innerHTML = '&#x25B2; Ocultar';
    });

    contenido.addEventListener('hidden.bs.collapse', () => {
        icon.innerHTML = '&#x25BC; Ver más';
    });
</script>
</body>
</html>
