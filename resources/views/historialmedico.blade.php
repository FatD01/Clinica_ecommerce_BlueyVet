<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/Vet/views/seccionesactivas.css'])
    @vite(['resources/css/Vet/views/historialmedico.css']) 
    @vite(['resources/css/vet/panel.css'])

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">


    <style>
        .mascota-link {
            text-decoration: none;
            color: inherit;
            display: block;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            transition: background-color 0.2s ease-in-out;
        }
        .mascota-link:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<div class="container mt-4">

 <!-- Sidebar -->
    <aside class="sidebar">
    <div class="brand">
        <i class="fas fa-paw"></i> BlueyVet
    </div>
    <nav>
        <ul>
            <li>
                <a href="{{ route('veterinarian.citas') }}"
                   class="{{ request()->routeIs('veterinarian.citas') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Consultar Citas
                </a>
            </li>
            <li>
                <a href="{{ route('historialmedico.index') }}"
                   class="{{ request()->routeIs('historialmedico.index') ? 'active' : '' }}">
                    <i class="fas fa-file-medical-alt"></i> Historial Médico
                </a>
            </li>
            <li>
                <a href="{{ route('veterinarian.profile') }}"
                   class="{{ request()->routeIs('veterinarian.profile*') || request()->routeIs('veterinarian.edit') ? 'active' : '' }}">
                    <i class="fas fa-user"></i> Mi Información
                </a>
            </li>
               <li>
                        <a href="{{ route('datosestadisticos') }}" class="{{ request()->routeIs('datosestadisticos') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i> Datos estadísticos
                        </a>
                    </li>
                    <li>
                <a href="{{ route('veterinarian.notificaciones') }}"
                   class="{{ request()->routeIs('veterinarian.notificaciones') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i> Notificaciones
                    @if($unreadCount)
                        <span class="notification-dot"></span>
                    @endif
                </a>
            </li>
        </ul>
    </nav>
    <div class="user">
        Hola, <strong>{{ Auth::user()->name }}</strong>
    </div>
</aside>
    {{-- Botón Atrás elegante --}}

     <div class="main-content container mt-4">
    <a href="{{ route('index') }}" class="btn btn-outline-secondary mb-3">
    &#8592; Atrás
</a>



<i class="fas fa-file-medical-alt"></i> Historial Médico de las Mascotas
    {{-- Formulario con select para elegir mascota --}}
    <form method="GET" id="filtroForm" class="row g-3 mb-4">
        <div class="col-md-10">
            <select name="mascota_id" class="form-select" id="mascotaSelect">
                <option value="">Selecciona una mascota...</option>
                @foreach ($todasMascotas as $mascota)
                    <option value="{{ $mascota->id }}" {{ request('mascota_id') == $mascota->id ? 'selected' : '' }}>
                        {{ $mascota->name }} ({{ $mascota->species }} - {{ $mascota->race }}) - {{ $mascota->cliente->user->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    @if ($mascotas->isEmpty())
        <div class="alert alert-info">No se encontraron mascotas asignadas con historial.</div>
    @else
        <div class="list-group">
            @foreach ($mascotas as $mascota)
                <a href="{{ route('veterinarian.historial', $mascota->id) }}" class="mascota-link">
                    <h5 class="mb-1">{{ $mascota->name }}</h5>
                    <small>Especie: {{ $mascota->species }} | Raza: {{ $mascota->race }}</small>
                </a>
            @endforeach
        </div>
    @endif
</div>

<script>
    document.getElementById('mascotaSelect').addEventListener('change', function () {
        document.getElementById('filtroForm').submit();
    });


</script>
</body>
</html>
