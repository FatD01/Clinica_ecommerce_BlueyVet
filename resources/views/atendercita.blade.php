<!DOCTYPE html>
<html>
<head>
    <title>Atender Cita</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @vite('resources/css/vet/views/atendercita.css') 
    @vite('resources/css/vet/views/seccionesactivas.css') 
    @vite(['resources/css/Vet/panel.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

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

<div class="main-content container mt-4">
    <h2 class="mb-4">Formulario de atención médica</h2>

    <div class="card mb-3">
        <div class="card-header">Información del Cliente</div>
        <div class="card-body">
            <p><strong>Nombre:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
            <p><strong>Email:</strong> {{ $usuario->email ?? 'No disponible' }}</p>
            <p><strong>Teléfono:</strong> {{ $cliente->telefono ?? 'No registrado' }}</p>
            <p><strong>Dirección:</strong> {{ $cliente->direccion ?? 'No registrada' }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Información de la Mascota</div>
        <div class="card-body">
            <p><strong>Nombre:</strong> {{ $mascota->name }}</p>
            <p><strong>Especie:</strong> {{ $mascota->species }}</p>
            <p><strong>Raza:</strong> {{ $mascota->race }}</p>
            <p><strong>Peso:</strong> {{ $mascota->weight }} kg</p>
            <p><strong>Nacimiento:</strong> {{ $mascota->birth_date }}</p>
            <p><strong>Alergias:</strong> {{ $mascota->allergies }}</p>
            @if($mascota->image)
                <img src="{{ asset('storage/' . $mascota->image) }}" alt="Imagen" class="img-fluid" style="max-width: 200px;">
            @endif
            <hr>
            <p><strong>Razón de la cita:</strong> {{ $appointment->reason }}</p>
            <p><strong>Fecha de la cita:</strong> {{ $appointment->date }}</p>
        </div>
    </div>


    
    <form method="POST" action="{{ route('veterinarian.guardar.atencion') }}">

        @csrf
        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">


        <div class="mb-3">
            <label for="diagnosis" class="form-label">Diagnóstico</label>
            <textarea name="diagnosis" class="form-control" rows="2">{{ old('diagnosis') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="treatment" class="form-label">Tratamiento</label>
            <textarea name="treatment" class="form-control" rows="2">{{ old('treatment') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notas</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="prescription" class="form-label">Receta médica</label>
            <textarea name="prescription" class="form-control" rows="2">{{ old('prescription') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="observations" class="form-label">Observaciones</label>
            <textarea name="observations" class="form-control" rows="2">{{ old('observations') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Enviar</button>
        <a href="{{ route('veterinarian.citas') }}" class="btn btn-secondary">← Cancelar</a>
    </form>
</div>
</body>
</html>
