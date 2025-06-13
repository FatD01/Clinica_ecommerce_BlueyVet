{{-- resources/views/client/citas/create.blade.php --}}

@extends('layouts.app') {{-- O tu layout base --}}

@section('content')
<div class="container">
    <h1>Agendar Nueva Cita</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('client.citas.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="mascota_id" class="form-label">Mascota:</label>
            <select name="mascota_id" id="mascota_id" class="form-control" required>
                <option value="">Selecciona tu mascota</option>
                @foreach($mascotas as $mascota)
                    <option value="{{ $mascota->id }}" {{ old('mascota_id') == $mascota->id ? 'selected' : '' }}>{{ $mascota->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="veterinarian_id" class="form-label">Veterinario:</label>
            <select name="veterinarian_id" id="veterinarian_id" class="form-control" required>
                <option value="">Selecciona un veterinario</option>
                @foreach($veterinarians as $veterinarian)
                    <option value="{{ $veterinarian->id }}" {{ old('veterinarian_id') == $veterinarian->id ? 'selected' : '' }}>{{ $veterinarian->user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Fecha y Hora:</label>
            <input type="datetime-local" name="date" id="date" class="form-control" value="{{ old('date') }}" required>
        </div>

        <div class="mb-3">
            <label for="service_id" class="form-label">Servicio:</label>
            <select name="service_id" id="service_id" class="form-control" required>
                <option value="">Selecciona un servicio</option>
                @foreach($allServices as $service)
                    <option value="{{ $service->id }}"
                        {{ (isset($preselectedService) && $preselectedService->id == $service->id) ? 'selected' : '' }}
                        {{ old('service_id') == $service->id ? 'selected' : '' }}
                    >
                        {{ $service->name }} (S/{{ number_format($service->price, 2) }})
                    </option>
                @endforeach
            </select>
            @if(isset($preselectedService))
                <small class="form-text text-muted">Este servicio está preseleccionado porque fue parte de tu compra o de la navegación.</small>
            @endif
        </div>

        <div class="mb-3">
            <label for="reason" class="form-label">Motivo de la Cita (Opcional):</label>
            <textarea name="reason" id="reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Agendar Cita</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const datetimeField = document.getElementById('date');
        const now = new Date();
        now.setMinutes(now.getMinutes() + 1); // Agrega 1 minuto para evitar seleccionar el segundo exacto actual
        const minDate = now.toISOString().slice(0, 16);
        datetimeField.setAttribute('min', minDate);
    });
</script>
@endpush