<!DOCTYPE html>
<html>
<head>
    <title>Citas agendadas</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fuente Poppins desde Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Tus estilos personalizados -->
    <link rel="stylesheet" href="{{ asset('filtros.css') }}">
    <link rel="stylesheet" href="{{ asset('citasagendadas.css') }}">
    <link rel="stylesheet" href="{{ asset('seccionesactivas.css') }}">
    
</head>
<body>
    <div class="layout">
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
     class="{{ request()->routeIs('historialmedico.index') ? 'active' : '' }}">
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

        <!-- Contenido principal -->
        <div class="main-content container mt-4">
            <h2 class="mb-4">Clientes con citas asignadas</h2>

            {{-- Formulario de Filtros --}}
            <div class="filter-card mb-4">
                <form method="GET" action="{{ route('veterinarian.citas') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado de la cita</label>
                            <select name="status" id="estado" class="form-select">
                                <option value="">-- Todos --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="desde" class="form-label">Desde</label>
                            <input type="date" name="desde" id="desde" class="form-control" value="{{ request('desde') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="hasta" class="form-label">Hasta</label>
                            <input type="date" name="hasta" id="hasta" class="form-control" value="{{ request('hasta') }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="{{ route('veterinarian.citas') }}" class="btn btn-outline-secondary">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Filtros activos --}}
            @if(request()->has('status') || request()->has('desde') || request()->has('hasta'))
                <p class="filtro-activo-resumen">
                    Mostrando citas
                    @if(request('status'))
                        con estado <strong>
                            {{ request('status') == 'pending' ? 'Pendiente' : (request('status') == 'completed' ? 'Completada' : '') }}
                        </strong>
                    @endif
                    @if(request('desde'))
                        desde <strong>{{ request('desde') }}</strong>
                    @endif
                    @if(request('hasta'))
                        hasta <strong>{{ request('hasta') }}</strong>
                    @endif
                </p>
            @endif

            {{-- Lista de clientes --}}
            @if($clientes->isEmpty())
                <p>No hay clientes con citas agendadas.</p>
            @else
                @foreach ($clientes as $cliente)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">{{ $cliente->nombre }} {{ $cliente->apellido }}</h5>
                            <p><strong>Email:</strong> {{ $cliente->user->email ?? 'No disponible' }}</p>
                            <p><strong>Teléfono:</strong> {{ $cliente->telefono ?? 'No registrado' }}</p>
                            <p><strong>Dirección:</strong> {{ $cliente->direccion ?? 'No registrada' }}</p>
                            <a href="{{ route('ver.mascotas', ['id' => $cliente->id]) }}" class="btn btn-primary">Ver mascotas</a>
                        </div>
                    </div>
                @endforeach
            @endif

            <a href="{{ route('index') }}" class="btn btn-secondary">← Volver</a>
        </div>
    </div>
</body>
</html>