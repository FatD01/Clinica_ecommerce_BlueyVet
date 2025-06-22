@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h2 class="mb-4 text-center">Reprogramar Cita</h2>

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
            <p><strong>Mascota:</strong> {{ $appointment->mascota->name }}</p>
            <p><strong>Veterinario:</strong> Dr(a). {{ $appointment->veterinarian->user->name }}</p>
            <p><strong>Fecha y Hora Actual:</strong> {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_datetime)->format('H:i') }}</p>
            <p><strong>Motivo:</strong> {{ $appointment->reason ?? 'No especificado' }}</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Propón una Nueva Fecha y Hora</div>
        <div class="card-body">
            <form action="{{ route('client.citas.reprogram.submit', $appointment->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="proposed_start_date_time" class="form-label">Nueva Fecha y Hora de Inicio:</label>
                    <input type="datetime-local" id="proposed_start_date_time" name="proposed_start_date_time"
                           class="form-control @error('proposed_start_date_time') is-invalid @enderror"
                           value="{{ old('proposed_start_date_time', Carbon\Carbon::parse($appointment->date)->format('Y-m-d\TH:i')) }}" required>
                    @error('proposed_start_date_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

              

                <div class="mb-3">
                    <label for="reprogramming_reason" class="form-label">Motivo de la Reprogramación:</label>
                    <textarea id="reprogramming_reason" name="reprogramming_reason" rows="3"
                              class="form-control @error('reprogramming_reason') is-invalid @enderror"
                              placeholder="Explica por qué necesitas reprogramar la cita.">{{ old('reprogramming_reason') }}</textarea>
                    @error('reprogramming_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-success">Enviar Solicitud</button>
                    <a href="{{ route('client.citas.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection