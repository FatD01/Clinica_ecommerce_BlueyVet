<!DOCTYPE html>
<html>
<head>
    <title>Mascotas con citas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('vermascotas.css') }}">
    <link rel="stylesheet" href="{{ asset('seccionesactivas.css') }}">
    
</head>
<body>
<div class="container mt-4">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="brand">BlueyVet</div>
        <nav>
            <ul>
                <li>
                    <a href="{{ route('veterinarian.citas') }}"
                       class="{{ request()->routeIs('veterinarian.citas*') || request()->routeIs('ver.mascotas') ? 'active' : '' }}">
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
        <h2 class="mb-4">Mascotas con citas agendadas de {{ $cliente->nombre }} {{ $cliente->apellido }}</h2>

        @if($mascotas->isEmpty())
            <p>Este cliente no tiene mascotas con citas pendientes.</p>
        @else
            @foreach ($mascotas as $mascota)
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $mascota->name }}</h5>
                        <p><strong>Especie:</strong> {{ $mascota->species }}</p>
                        <p><strong>Raza:</strong> {{ $mascota->race }}</p>

                        <div class="collapse toggle-section" id="detalles-{{ $mascota->id }}">
                            <hr>
                            <p><strong>Peso:</strong> {{ $mascota->weight }} kg</p>
                            <p><strong>Nacimiento:</strong> {{ $mascota->birth_date }}</p>
                            <p><strong>Alergias:</strong> {{ $mascota->allergies }}</p>

                            @if ($mascota->image)
                                <img src="{{ asset('storage/' . $mascota->image) }}" alt="Imagen"
                                     class="img-fluid mb-2" style="max-width: 200px;">
                            @endif

                            @if ($mascota->appointments->isNotEmpty())
    @foreach ($mascota->appointments as $cita)
        <div class="mb-3 p-3 border rounded bg-light">
            <p><strong>Razón de la cita:</strong> {{ $cita->reason }}</p>
            <p><strong>Fecha de la cita:</strong> {{ $cita->date }}</p>
            <a href="{{ route('veterinarian.atender', $cita->id) }}"
               class="btn btn-atender mt-2">
               Atender Cita
            </a>
        </div>
    @endforeach
@else
    <p>No hay citas pendientes para esta mascota.</p>
@endif

                        </div>
                    </div>
                    <button class="btn btn-primary w-100 btn-toggle"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#detalles-{{ $mascota->id }}"
                            aria-expanded="false"
                            aria-controls="detalles-{{ $mascota->id }}">
                        Mostrar más
                    </button>
                </div>
            @endforeach
        @endif

        <a href="{{ route('veterinarian.citas') }}" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.btn-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-bs-target');
            const collapseEl = document.querySelector(targetId);

            const isExpanded = collapseEl.classList.contains('show');
            this.textContent = isExpanded ? 'Mostrar más' : 'Ocultar';

            const observer = new MutationObserver(() => {
                this.textContent = collapseEl.classList.contains('show') ? 'Ocultar' : 'Mostrar más';
            });

            observer.observe(collapseEl, { attributes: true, attributeFilter: ['class'] });
        });
    });
</script>
</body>
</html>
