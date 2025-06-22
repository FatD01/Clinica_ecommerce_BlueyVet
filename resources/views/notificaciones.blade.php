@php use Carbon\Carbon; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - BlueyVet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @vite('resources/css/vet/views/seccionesactivas.css')
    @vite(['resources/css/Vet/panel.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Tus estilos existentes */
        .notificaciones-container {
            margin-left: 250px;
            padding: 2rem;
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
            min-height: 100vh;
        }

        .titulo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.6rem;
            font-weight: 500;
            color: #2d3436;
            margin-bottom: 2rem;
            border-left: 4px solid #3498db;
            padding-left: 1rem;
            background-color: #ecf3f9;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .titulo i {
            color: #3498db;
            font-size: 1.4rem;
        }

        .notificacion-card {
            background: #ffffff;
            border-left: 5px solid;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .notificacion-card:hover {
            transform: translateY(-4px);
        }

        .notificacion-card h5 {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }

        .notificacion-card p {
            color: #34495e;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .notificacion-card i {
            color: #3498db;
            margin-right: 6px;
        }

        .notificacion-card .btn {
            margin-top: 0.75rem;
            border-radius: 20px;
            padding: 0.4rem 1.2rem;
            font-size: 0.9rem;
        }

        /* NUEVOS ESTILOS PARA NOTIFICACIONES DE REPROGRAMACIÓN */
        .notification-reprogramming-container .notificacion-card {
            border-left: 5px solid;
        }
        .notification-pending-vet {
            border-color: #ffc107; /* Amarillo para pendiente por veterinario */
        }
        .notification-pending-vet h5 {
            color: #ffc107; /* Título amarillo */
        }
        .notification-pending-client {
            border-color: #17a2b8; /* Azul claro para pendiente por cliente */
        }
        .notification-pending-client h5 {
            color: #17a2b8; /* Título azul claro */
        }
        .notification-accepted {
            border-color: #28a745; /* Verde para aceptado */
        }
        .notification-rejected {
            border-color: #dc3545; /* Rojo para rechazado */
        }
        .notification-cancelled {
            border-color: #6c757d; /* Gris para cancelado */
        }
        .notification-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: red;
            border-radius: 50%;
            margin-left: 5px;
            vertical-align: middle;
        }
        .d-flex.flex-wrap.gap-2 { /* Para los botones */
            gap: 0.5rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

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
                <a href="{{ route('datosestadisticos') }}"
                   class="{{ request()->routeIs('datosestadisticos') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Datos estadísticos
                </a>
            </li>
            <li>
                <a href="{{ route('veterinarian.notificaciones') }}"
                   class="{{ request()->routeIs('veterinarian.notificaciones') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i> Notificaciones
                    @if(isset($unreadCount) && $unreadCount > 0)
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

<main class="notificaciones-container">
    <div class="titulo">
        <i class="fas fa-bell"></i> Tus Notificaciones
    </div>

    {{-- Seccion de NOTIFICACIONES DE REPROGRAMACIÓN --}}
    <h3 class="mb-3">Solicitudes de Reprogramación</h3>
    @if($reprogrammingRequests->isEmpty())
        <div class="alert alert-info">No tienes solicitudes de reprogramación pendientes en este momento.</div>
    @else
        <div class="notification-reprogramming-container">
            @foreach($reprogrammingRequests as $request)
                <div class="notificacion-card
                    @if($request->status == 'pending_veterinarian_confirmation') notification-pending-vet
                    @elseif($request->status == 'pending_client_confirmation') notification-pending-client
                    @elseif($request->status == 'accepted') notification-accepted
                    @elseif($request->status == 'rejected' || $request->status == 'cancelled') notification-rejected
                    @endif">
                    <div class="card-body">
                        @if($request->status == 'pending_veterinarian_confirmation')
                            {{-- Situación 1: Cliente ha enviado propuesta (Veterinario debe responder) --}}
                            <h5 class="card-title"><i class="fas fa-exclamation-circle"></i> ¡Nueva Propuesta de Reprogramación!</h5>
                            <p class="card-text">
                                El cliente <strong>{{ $request->client->nombre ?? 'Desconocido' }} {{ $request->client->apellido ?? '' }}</strong> {{-- MODIFICADO AQUÍ --}}
                                (Mascota: <strong>{{ $request->appointment->mascota->name ?? 'Desconocido' }}</strong>)
                                ha propuesto un nuevo horario para su cita original del
                                <strong>{{ Carbon::parse($request->appointment->date)->format('d/m/Y H:i') }}</strong>.
                            </p>
                            <p class="card-text">
                                <strong>Nuevo Horario Propuesto:</strong>
                                {{ Carbon::parse($request->proposed_start_date_time)->format('d/m/Y H:i') }}
                                @if($request->proposed_end_date_time)
                                    - {{ Carbon::parse($request->proposed_end_date_time)->format('H:i') }}
                                @endif
                            </p>
                            @if($request->reprogramming_reason)
                                <p class="card-text"><small class="text-muted">Motivo del cliente: <em>"{{ $request->reprogramming_reason }}"</em></small></p>
                            @endif

                            <div class="d-flex flex-wrap gap-2">
                                {{-- Botón Aceptar y Confirmar --}}
                                <form action="{{ route('veterinarian.reprogramacion.aceptar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="request_id" value="{{ $request->id }}">
                                    <button type="submit" class="btn btn-success btn-sm">Aceptar y Confirmar</button>
                                </form>

                                {{-- Botón Proponer Otro Horario (abre modal para nueva propuesta) --}}
                                <button type="button" class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalProponerOtroHorario"
                                        data-request-id="{{ $request->id }}"
                                        data-appointment-id="{{ $request->appointment->id }}">
                                    Proponer Otro Horario
                                </button>

                                {{-- Botón Cancelar Cita Definitivamente --}}
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCancelarCitaDefinitivamente"
                                        data-appointment-id="{{ $request->appointment->id }}">
                                    Cancelar Cita Definitivamente
                                </button>
                            </div>

                        @elseif($request->status == 'pending_client_confirmation')
                            {{-- Situación 2: Veterinario ha enviado propuesta (esperando respuesta del Cliente) --}}
                            <h5 class="card-title"><i class="fas fa-hourglass-half"></i> Esperando Confirmación del Cliente</h5>
                            <p class="card-text">
                                Has propuesto un nuevo horario para la cita de
                                <strong>{{ $request->appointment->mascota->name ?? 'Desconocido' }}</strong>
                                (Cliente: <strong>{{ $request->client->nombre ?? 'Desconocido' }} {{ $request->client->apellido ?? '' }}</strong>) para: {{-- MODIFICADO AQUÍ --}}
                                <strong>{{ Carbon::parse($request->proposed_start_date_time)->format('d/m/Y H:i') }}</strong>.
                                Estamos esperando la confirmación del cliente.
                            </p>
                            @if($request->reprogramming_reason)
                                <p class="card-text"><small class="text-muted">Tu motivo: <em>"{{ $request->reprogramming_reason }}"</em></small></p>
                            @endif

                            <div class="d-flex flex-wrap gap-2">
                                {{-- Botón Retirar mi Propuesta y Cancelar (solo la solicitud, no la cita) --}}
                                <form action="{{ route('veterinarian.reprogramacion.retirar_propuesta') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="request_id" value="{{ $request->id }}">
                                    <button type="submit" class="btn btn-secondary btn-sm">Retirar mi Propuesta</button>
                                </form>

                                {{-- Botón Cancelar Cita Definitivamente --}}
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCancelarCitaDefinitivamente"
                                        data-appointment-id="{{ $request->appointment->id }}">
                                    Cancelar Cita Definitivamente
                                </button>
                            </div>

                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <hr class="my-4">

    {{-- Seccion de NOTIFICACIONES DE CITAS PARA MAÑANA (tu codigo original) --}}
    <h3 class="mb-3">Citas Programadas para Mañana</h3>
    @if($citas->isEmpty())
        <div class="alert alert-info">No hay citas programadas para mañana.</div>
    @else
        @foreach($citas as $cita)
            <div class="notificacion-card">
                <h5><i class="fas fa-user"></i> Cliente:
                    {{ $cita->mascota->cliente->nombre ?? 'Desconocido' }} {{ $cita->mascota->cliente->apellido ?? '' }} {{-- MODIFICADO AQUÍ --}}
                </h5>
                <p><strong><i class="fas fa-dog"></i> Mascota:</strong> {{ $cita->mascota->name }}</p>
                <p><strong><i class="fas fa-stethoscope"></i> Motivo:</strong> {{ $cita->reason ?? 'Sin motivo especificado' }}</p>
                <p><strong><i class="fas fa-clock"></i> Hora:</strong> {{ \Carbon\Carbon::parse($cita->date)->format('H:i') }}</p>
                <a href="{{ route('ver.mascotas', ['id' => $cita->mascota->cliente->id, 'cita' => $cita->id]) }}" class="btn btn-primary btn-sm"> {{-- MODIFICADO AQUÍ --}}
                    <i class="fas fa-eye"></i> Ver Mascotas
                </a>
            </div>
        @endforeach
    @endif
</main>

{{-- Modales (sin cambios en la lógica de acceso a datos aquí) --}}
<div class="modal fade" id="modalProponerOtroHorario" tabindex="-1" aria-labelledby="modalProponerOtroHorarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('veterinarian.reprogramar.cita') }}">
            @csrf
            <input type="hidden" name="original_request_id" id="modal_original_request_id">
            <input type="hidden" name="appointment_id" id="modal_proponer_otro_appointment_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proponer Otro Horario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>Propón un nuevo horario para la cita.</p>
                    <label for="nueva_fecha_otro" class="form-label">Nueva fecha y hora propuesta</label>
                    <input type="datetime-local" class="form-control" name="nueva_fecha" id="nueva_fecha_otro" required>

                    <label for="reprogramming_reason_otro" class="form-label mt-3">Motivo de la contrapropuesta (opcional)</label>
                    <textarea class="form-control" name="reprogramming_reason" id="reprogramming_reason_otro" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Enviar Contrapropuesta</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalCancelarCitaDefinitivamente" tabindex="-1" aria-labelledby="modalCancelarCitaDefinitivamenteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('veterinarian.cancelar.cita') }}">
            @csrf
            <input type="hidden" name="appointment_id" id="modal_cancelar_cita_definitivamente_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancelar Cita Definitivamente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">Esta acción cancelará la cita de forma permanente.</p>
                    <label for="motivo_cancelacion_definitiva" class="form-label">Motivo de cancelación</label>
                    <textarea class="form-control" name="motivo" id="motivo_cancelacion_definitiva" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalProponerOtroHorario = document.getElementById('modalProponerOtroHorario');
        if (modalProponerOtroHorario) {
            modalProponerOtroHorario.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const requestId = button?.getAttribute('data-request-id') ?? null;
                const appointmentId = button?.getAttribute('data-appointment-id') ?? null;

                const originalRequestIdInput = modalProponerOtroHorario.querySelector('#modal_original_request_id');
                const appointmentIdInput = modalProponerOtroHorario.querySelector('#modal_proponer_otro_appointment_id');

                if (originalRequestIdInput) originalRequestIdInput.value = requestId;
                if (appointmentIdInput) appointmentIdInput.value = appointmentId;
            });
        }

        const modalCancelarCitaDefinitivamente = document.getElementById('modalCancelarCitaDefinitivamente');
        if (modalCancelarCitaDefinitivamente) {
            modalCancelarCitaDefinitivamente.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const appointmentId = button?.getAttribute('data-appointment-id') ?? null;
                const input = modalCancelarCitaDefinitivamente.querySelector('#modal_cancelar_cita_definitivamente_id');
                if (input && appointmentId) {
                    input.value = appointmentId;
                }
            });
        }
    });
</script>
</body>
</html>