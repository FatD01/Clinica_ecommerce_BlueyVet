@extends('layouts.app') {{-- Asegúrate de que esto coincide con tu layout principal --}}

@section('content')
<div class="container my-4">
    <h2 class="mb-4">Agendar Nueva Cita</h2>

    {{-- Mensajes de sesión --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">Completa los datos de tu cita</div>
        <div class="card-body">
            <form action="{{ route('client.citas.store') }}" method="POST">
                @csrf

                {{-- Campo para seleccionar mascota --}}
                <div class="mb-3">
                    <label for="mascota_id" class="form-label">Mascota</label>
                    <select class="form-select @error('mascota_id') is-invalid @enderror" id="mascota_id" name="mascota_id" required>
                        <option value="">Selecciona una mascota</option>
                        {{-- La variable $mascotas viene del CitaController@create --}}
                        @foreach($mascotas as $mascota)
                            <option value="{{ $mascota->id }}" {{ old('mascota_id') == $mascota->id ? 'selected' : '' }}>
                                {{ $mascota->name }} ({{ $mascota->species }})
                            </option>
                        @endforeach
                    </select>
                    @error('mascota_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo para seleccionar servicio --}}
                <div class="mb-3">
                    <label for="service_id" class="form-label">Servicio</label>
                    <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id" required>
                        <option value="">Selecciona un servicio</option>
                        {{-- La variable $allServices viene del CitaController@create --}}
                        @foreach($allServices as $service)
                            <option value="{{ $service->id }}" 
                                    data-price="{{ $service->price }}" 
                                    {{ (old('service_id') == $service->id || ($preselectedService && $preselectedService->id == $service->id)) ? 'selected' : '' }}>
                                {{ $service->name }} - S/{{ number_format($service->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo para seleccionar veterinario --}}
                <div class="mb-3">
                    <label for="veterinarian_id" class="form-label">Veterinario</label>
                    <select class="form-select @error('veterinarian_id') is-invalid @enderror" id="veterinarian_id" name="veterinarian_id" required>
                        <option value="">Selecciona un veterinario</option>
                        {{-- La variable $veterinarians viene del CitaController@create --}}
                        @foreach($veterinarians as $veterinarian)
                            {{-- Aquí accedemos al nombre del usuario del veterinario --}}
                            <option value="{{ $veterinarian->id }}" {{ old('veterinarian_id') == $veterinarian->id ? 'selected' : '' }}>
                                {{ $veterinarian->user->name ?? 'N/A' }} ({{ $veterinarian->specialty ?? 'Sin especialidad' }})
                            </option>
                        @endforeach
                    </select>
                    @error('veterinarian_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo para la fecha y hora --}}
                <div class="mb-3">
                    <label for="date" class="form-label">Fecha y Hora</label>
                    <input type="datetime-local" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date') }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo para el motivo --}}
                <div class="mb-3">
                    <label for="reason" class="form-label">Motivo de la Cita (opcional)</label>
                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3">{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Agendar Cita</button>
                <a href="{{ route('client.citas.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Si tienes algún script específico para el formulario (ej. datepicker), va aquí --}}
{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicialización de datepicker, etc.
    });
</script> --}}
@endpush