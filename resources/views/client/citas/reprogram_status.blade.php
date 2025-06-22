@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h2 class="mb-4 text-center">Estado de la Solicitud de Reprogramación</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">Detalles de la Cita Original</div>
        <div class="card-body">
            <p><strong>Mascota:</strong> {{ $reprogrammingRequest->appointment->mascota->name }}</p>
            <p><strong>Veterinario:</strong> Dr(a). {{ $reprogrammingRequest->appointment->veterinarian->user->name }}</p>
            <p><strong>Fecha y Hora Original:</strong> {{ \Carbon\Carbon::parse($reprogrammingRequest->appointment->date)->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($reprogrammingRequest->appointment->end_datetime)->format('H:i') }}</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Detalles de la Solicitud de Reprogramación</div>
        <div class="card-body">
            <p><strong>Fecha y Hora Propuesta:</strong> {{ \Carbon\Carbon::parse($reprogrammingRequest->proposed_start_date_time)->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($reprogrammingRequest->proposed_end_date_time)->format('H:i') }}</p>
            <p><strong>Motivo:</strong> {{ $reprogrammingRequest->reprogramming_reason ?? 'No especificado' }}</p>
            <p><strong>Solicitante:</strong> {{ ucfirst($reprogrammingRequest->requester_type) }}</p>
            <p><strong>Estado:</strong>
                <span class="badge {{
                    $reprogrammingRequest->status == 'pending_client_confirmation' ? 'bg-warning' :
                    ($reprogrammingRequest->status == 'pending_veterinarian_confirmation' ? 'bg-info' :
                    ($reprogrammingRequest->status == 'accepted_by_both' ? 'bg-success' :
                    ($reprogrammingRequest->status == 'rejected_by_client' || $reprogrammingRequest->status == 'rejected_by_veterinarian' ? 'bg-danger' :
                    ($reprogrammingRequest->status == 'applied' ? 'bg-primary' :
                    ($reprogrammingRequest->status == 'cancelled_by_request' ? 'bg-secondary' : 'bg-light text-dark')))))
                }}">
                    {{ ucfirst(str_replace('_', ' ', $reprogrammingRequest->status)) }}
                </span>
            </p>

            @if(!empty($reprogrammingRequest->admin_notes))
                <p><strong>Notas del Administrador:</strong> {{ $reprogrammingRequest->admin_notes }}</p>
            @endif

            <div class="mt-4 text-center">
                @if($reprogrammingRequest->status == 'pending_client_confirmation' && $reprogrammingRequest->requester_type == 'veterinarian')
                    <p class="mb-3">El veterinario ha propuesto una nueva fecha para su cita. ¿Aceptas?</p>
                    <form action="{{ route('client.reprogram.confirm', $reprogrammingRequest->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" name="action" value="accept" class="btn btn-success me-2">Aceptar Propuesta</button>
                    </form>
                    <form action="{{ route('client.reprogram.confirm', $reprogrammingRequest->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" name="action" value="reject" class="btn btn-danger">Rechazar Propuesta</button>
                    </form>
                @elseif($reprogrammingRequest->status == 'pending_veterinarian_confirmation' && $reprogrammingRequest->requester_type == 'client')
                    <p class="text-info">Tu solicitud está pendiente de la confirmación del veterinario.</p>
                    <button type="button" class="btn btn-secondary" disabled>Esperando respuesta del veterinario...</button>
                @elseif($reprogrammingRequest->status == 'accepted_by_both')
                    <p class="text-success">¡La reprogramación ha sido aceptada por ambas partes!</p>
                    <p class="text-muted">La cita original será actualizada a la nueva fecha y hora pronto.</p>
                @elseif($reprogrammingRequest->status == 'applied')
                    <p class="text-primary">¡La cita ha sido reprogramada exitosamente!</p>
                    <p class="text-muted">Revisa tus citas para ver los detalles actualizados.</p>
                @elseif($reprogrammingRequest->status == 'rejected_by_veterinarian')
                    <p class="text-danger">El veterinario ha rechazado tu solicitud de reprogramación.</p>
                    <a href="{{ route('client.reprogram.form', $reprogrammingRequest->appointment->id) }}" class="btn btn-warning">Proponer Otra Fecha</a>
                @elseif($reprogrammingRequest->status == 'rejected_by_client')
                    <p class="text-danger">Has rechazado la propuesta de reprogramación del veterinario.</p>
                    <a href="{{ route('client.reprogram.form', $reprogrammingRequest->appointment->id) }}" class="btn btn-warning">Proponer Otra Fecha</a>
                @endif
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <a href="{{ route('client.citas.index') }}" class="btn btn-primary">Volver a Mis Citas</a>
    </div>
</div>
@endsection