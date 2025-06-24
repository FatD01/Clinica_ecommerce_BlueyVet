@extends('layouts.app') {{-- Asegúrate de que tu layout base se llama 'app' --}}

@section('title', 'Estado de Reprogramación de Cita')

@push('styles')
    <style>
        .status-badge {
            padding: 0.5em 0.8em;
            border-radius: 0.25rem;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending { background-color: #ffc107; color: #343a40; } /* Amarillo para pendiente */
        .status-proposed { background-color: #17a2b8; color: #fff; } /* Azul cian para propuesta del veterinario */
        .status-accepted { background-color: #28a745; color: #fff; } /* Verde para aceptado */
        .status-rejected, .status-cancelled, .status-retracted { background-color: #dc3545; color: #fff; } /* Rojo para rechazado/cancelado/retirado */
        .status-completed { background-color: #6c757d; color: #fff; } /* Gris para completado */
    </style>
@endpush

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Estado de Reprogramación de Cita #{{ $appointment->id }}</h2>
        <a href="{{ route('client.citas.index') }}" class="btn btn-outline-secondary">Volver a mis Citas</a>
    </div>

    {{-- Mensajes de sesión --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Detalles de la Cita Original</h5>
        </div>
        <div class="card-body">
            <p><strong>Mascota:</strong> {{ $appointment->mascota->name }}</p>
            <p><strong>Veterinario:</strong> {{ $appointment->veterinarian->user->name }} {{ $appointment->veterinarian->user->last_name }}</p>
            <p><strong>Servicio:</strong> {{ $appointment->service->name }}</p>
            <p><strong>Fecha y Hora Actual:</strong> {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @if ($reprogrammingRequest)
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Detalles de la Solicitud de Reprogramación</h5>
            </div>
            <div class="card-body">
                <p><strong>Estado:</strong>
                    @php
                        $statusClass = '';
                        $statusText = '';
                        switch ($reprogrammingRequest->status) {
                            case 'pending':
                                $statusClass = 'status-pending';
                                $statusText = 'Pendiente de respuesta del veterinario';
                                break;
                            case 'veterinarian_proposed':
                                $statusClass = 'status-proposed';
                                $statusText = 'Veterinario ha hecho una contrapropuesta';
                                break;
                            case 'accepted':
                                $statusClass = 'status-accepted';
                                $statusText = 'Reprogramación Aceptada';
                                break;
                            case 'rejected':
                                $statusClass = 'status-rejected';
                                $statusText = 'Solicitud Rechazada';
                                break;
                            case 'cancelled':
                                $statusClass = 'status-cancelled';
                                $statusText = 'Solicitud Cancelada';
                                break;
                            case 'client_retracted':
                                $statusClass = 'status-retracted';
                                $statusText = 'Solicitud Retirada por ti';
                                break;
                            default:
                                $statusClass = 'status-info';
                                $statusText = 'Desconocido';
                        }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </p>
                <p>
                    <strong>Tu Propuesta Original:</strong>
                    {{ \Carbon\Carbon::parse($reprogrammingRequest->client_proposed_date_time)->format('d/m/Y H:i') }}
                    <br>
                    <small class="text-muted">Motivo: {{ $reprogrammingRequest->client_reason }}</small>
                </p>

                @if ($reprogrammingRequest->veterinarian_proposed_date_time)
                    <p>
                        <strong>Propuesta del Veterinario:</strong>
                        {{ \Carbon\Carbon::parse($reprogrammingRequest->veterinarian_proposed_date_time)->format('d/m/Y H:i') }}
                        <br>
                        <small class="text-muted">Notas del Veterinario: {{ $reprogrammingRequest->veterinarian_notes ?? 'Ninguna.' }}</small>
                    </p>
                @endif

                <p><strong>Solicitado el:</strong> {{ $reprogrammingRequest->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Última actualización:</strong> {{ $reprogrammingRequest->updated_at->format('d/m/Y H:i') }}</p>

                <hr>

                {{-- Acciones basadas en el estado de la solicitud --}}
                <div class="d-flex flex-wrap gap-2">
                    @if ($reprogrammingRequest->status === 'pending')
                        {{-- El cliente puede retirar su propia propuesta si está pendiente --}}
                        <form action="{{ route('client.citas.reprogram.retract_proposal', $reprogrammingRequest->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres retirar tu solicitud de reprogramación? Esto cancelará tu propuesta y dejará la cita original como estaba.')">
                            @csrf
                            <button type="submit" class="btn btn-warning">Retirar Solicitud Original</button>
                        </form>
                        <p class="ms-3 my-auto text-muted">Esperando la respuesta del veterinario.</p>
                    @elseif ($reprogrammingRequest->status === 'veterinarian_proposed')
                        {{-- El cliente puede aceptar o rechazar la contrapropuesta del veterinario --}}
                        <form action="{{ route('client.citas.reprogram.respond', $appointment->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres ACEPTAR la propuesta del veterinario? Esto reprogramará tu cita a la nueva fecha y hora.')">
                            @csrf
                            <input type="hidden" name="action" value="accept_veterinarian_proposal">
                            <input type="hidden" name="reprogramming_request_id" value="{{ $reprogrammingRequest->id }}">
                            <button type="submit" class="btn btn-success">Aceptar Propuesta del Veterinario</button>
                        </form>

                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectProposalModal">
                            Rechazar Propuesta / Proponer otra
                        </button>
                    @elseif ($reprogrammingRequest->status === 'accepted')
                        <p class="text-success fw-bold">¡Reprogramación confirmada! Tu cita ha sido actualizada.</p>
                    @elseif ($reprogrammingRequest->status === 'rejected')
                        <p class="text-danger fw-bold">El veterinario ha rechazado tu solicitud de reprogramación. La cita original se mantiene.</p>
                        <a href="{{ route('client.citas.reprogram.form', $appointment->id) }}" class="btn btn-primary">Hacer Nueva Solicitud de Reprogramación</a>
                    @elseif ($reprogrammingRequest->status === 'cancelled' || $reprogrammingRequest->status === 'client_retracted')
                        <p class="text-muted fw-bold">Esta solicitud de reprogramación ha sido {{ $statusText }}.</p>
                        <a href="{{ route('client.citas.reprogram.form', $appointment->id) }}" class="btn btn-primary">Iniciar Nueva Solicitud de Reprogramación</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center shadow-sm">
            <p class="mb-0">No hay una solicitud de reprogramación pendiente o activa para esta cita.</p>
            <a href="{{ route('client.citas.reprogram.form', $appointment->id) }}" class="btn btn-primary mt-3">Iniciar Solicitud de Reprogramación</a>
        </div>
    @endif
</div>

{{-- Modal para Rechazar Propuesta / Proponer Otra --}}
<div class="modal fade" id="rejectProposalModal" tabindex="-1" aria-labelledby="rejectProposalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('client.citas.reprogram.respond', $appointment->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="reject_veterinarian_proposal">
                <input type="hidden" name="reprogramming_request_id" value="{{ $reprogrammingRequest->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectProposalModalLabel">Rechazar Propuesta del Veterinario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Si rechazas esta propuesta, la solicitud de reprogramación finalizará y la cita original se mantendrá. Si deseas una nueva fecha, tendrás que iniciar una nueva solicitud.</p>
                    <p>¿Estás seguro de que quieres rechazar la propuesta del veterinario?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sí, Rechazar Propuesta</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Moment.js si lo necesitas para formato de fechas en JS, ya lo tienes en reprogram_form --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    {{-- Axios ya lo tienes en reprogram_form --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        // Si necesitas alguna interacción JS dinámica futura para este status, aquí iría.
        // Por ahora, las acciones son formularios simples.
    </script>
@endpush