@php use Carbon\Carbon; @endphp
<!DOCTYPE html>
<html>
<head>
    <title>Mascotas con citas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @vite('resources/css/vet/views/vermascotas.css')
    @vite('resources/css/vet/views/seccionesactivas.css')
    @vite(['resources/css/Vet/panel.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container mt-4">
    <aside class="sidebar">
        <div class="brand">
            <i class="fas fa-paw"></i> BlueyVet
        </div>
        <nav>
            <ul>
                <li><a href="{{ route('veterinarian.citas') }}" class="{{ request()->routeIs('veterinarian.citas') ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i> Consultar Citas</a></li>
                <li><a href="{{ route('historialmedico.index') }}" class="{{ request()->routeIs('historialmedico.index') ? 'active' : '' }}"><i class="fas fa-file-medical-alt"></i> Historial Médico</a></li>
                <li><a href="{{ route('veterinarian.profile') }}" class="{{ request()->routeIs('veterinarian.profile*') || request()->routeIs('veterinarian.edit') ? 'active' : '' }}"><i class="fas fa-user"></i> Mi Información</a></li>
                <li><a href="{{ route('datosestadisticos') }}" class="{{ request()->routeIs('datosestadisticos') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> Datos estadísticos</a></li>
                <li><a href="{{ route('veterinarian.notificaciones') }}" class="{{ request()->routeIs('veterinarian.notificaciones') ? 'active' : '' }}"><i class="fas fa-bell"></i> Notificaciones
                        @if($unreadCount > 0)
                            <span class="notification-dot"></span>
                        @endif
                    </a></li>
            </ul>
        </nav>
        <div class="user">Hola, <strong>{{ Auth::user()->name }}</strong></div>
    </aside>

    <div class="main-content container mt-4">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif
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

                           @php
    $cita = $citaEspecifica;
@endphp

                            @if ($cita)
                                @php
                                    $fechaCita = \Carbon\Carbon::parse($cita->date)->startOfDay();
                                    $hoy = now()->startOfDay();

                                @endphp
                                
                                @if ($fechaCita->lessThanOrEqualTo($hoy))

                                    <a href="{{ route('veterinarian.atender', $cita->id) }}"
                                       class="btn btn-secondary w-100 text-center"
                                       style="background-color: #ffc609; height: 45px; display: flex; align-items: center; justify-content: center;">
                                        Atender Cita
                                    </a>
                                @else
                                    <button type="button"
                                            class="btn btn-secondary w-100 text-center btn-restringido"
                                            style="background-color: #ffc609; height: 45px; display: flex; align-items: center; justify-content: center;">
                                        Atender Cita
                                    </button>
                                @endif

                                @if ($cita->status !== 'confirmed')
                                 <button type="button"
                                        class="btn btn-primary w-100 text-center"
                                        style="height: 45px; display: flex; align-items: center; justify-content: center; margin-top: 10px;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalReprogramarCita"
                                        data-cita-id="{{ $cita->id }}">
                                    Reprogramar Cita
                                </button>

                                <button type="button"
                                        class="btn btn-secondary w-100 text-center"
                                        style="height: 45px; display: flex; align-items: center; justify-content: center;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCancelarCita"
                                        data-cita-id="{{ $cita->id }}">
                                    Cancelar Cita
                                </button>
                            

                               
    <form method="POST" action="{{ route('veterinarian.confirmar.cita') }}">
        @csrf
        <input type="hidden" name="appointment_id" value="{{ $cita->id }}">
        <button type="submit"
            class="btn btn-success w-100 text-center"
            style="height: 45px; display: flex; align-items: center; justify-content: center; margin-top: 10px;">
            Confirmar Cita
        </button>
    </form>
    @else
                                <p>La cita ya ha sido confirmada.</p>
                        
@endif
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

        <a href="{{ route(name: 'veterinarian.citas') }}" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<div class="modal fade" id="modalCancelarCita" tabindex="-1" aria-labelledby="modalCancelarCitaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('veterinarian.cancelar.cita') }}">
            @csrf
            <input type="hidden" name="appointment_id" id="modal_cita_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancelar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label for="motivo" class="form-label">Motivo de cancelación</label>
                    <textarea class="form-control" name="motivo" id="motivo" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Enviar Cancelación</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalCitaRestringida" tabindex="-1" aria-labelledby="modalCitaRestringidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalCitaRestringidaLabel">🐾 Atención no disponible aún</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-3">Todavía no puedes atender esta cita...</p>
                <img src="{{ asset('img/perrito-no.png') }}" alt="Perrito negando" class="img-fluid" style="max-width: 100%; border-radius: 10px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalReprogramarCita" tabindex="-1" aria-labelledby="modalReprogramarCitaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('veterinarian.reprogramar.cita') }}">
            @csrf
            <input type="hidden" name="appointment_id" id="modal_reprogramar_cita_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reprogramar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label for="nueva_fecha" class="form-label">Nueva fecha y hora propuesta</label>
                    <input type="datetime-local" class="form-control" name="nueva_fecha" id="nueva_fecha" required>

                    {{-- ¡Nuevo campo para el motivo! --}}
                    <label for="reprogramming_reason" class="form-label mt-3">Motivo de la reprogramación (opcional)</label>
                    <textarea class="form-control" name="reprogramming_reason" id="reprogramming_reason" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Enviar Solicitud de Reprogramación</button>
                </div>
            </div>
        </form>
    </div>
</div>

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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('button.btn-restringido').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const modal = new bootstrap.Modal(document.getElementById('modalCitaRestringida'));
                modal.show();
            });
        });

        const modalCancelarCita = document.getElementById('modalCancelarCita');
        if (modalCancelarCita) {
            modalCancelarCita.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const citaId = button?.getAttribute('data-cita-id') ?? null;
                const input = modalCancelarCita.querySelector('#modal_cita_id');
                if (input && citaId) {
                    input.value = citaId;
                }
            });
        }
         // Configuración del modal de reprogramación
         const modalReprogramarCita = document.getElementById('modalReprogramarCita');
        if (modalReprogramarCita) {
            modalReprogramarCita.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const citaId = button?.getAttribute('data-cita-id') ?? null;
                const input = modalReprogramarCita.querySelector('#modal_reprogramar_cita_id');
                if (input && citaId) {
                    input.value = citaId;
                }
            });
        }
    });
</script>
</body>
</html>