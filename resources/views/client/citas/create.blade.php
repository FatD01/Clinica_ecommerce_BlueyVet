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
                                    data-duration="{{ $service->duration_minutes ?? 30 }}" {{-- Añadido data-duration --}}
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

                {{-- Campo para la FECHA (separado de la hora) --}}
                <div class="mb-3">
                    <label for="date_only_picker" class="form-label">Fecha de la Cita</label>
                    <input type="date" class="form-control @error('date_only_picker') is-invalid @enderror" 
                               id="date_only_picker" name="date_only_picker" 
                               value="{{ old('date_only_picker', \Carbon\Carbon::now()->format('Y-m-d')) }}" {{-- Valor por defecto al día actual --}}
                               min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                    @error('date_only_picker')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contenedor para los slots de tiempo disponibles. Inicialmente oculto. --}}
                <div class="mb-3" id="time-slots-container" style="display: none;">
                    <label for="time_slot" class="form-label">Hora de Inicio Disponible</label>
                    {{-- Este select es el que realmente envía la fecha y hora completa al controlador --}}
                    <select class="form-select @error('date') is-invalid @enderror" id="time_slot" name="date" required>
                        <option value="">Selecciona una hora</option>
                        {{-- Los slots se cargarán aquí con JavaScript --}}
                    </select>
                    <div id="no-slots-message" class="text-info mt-2" style="display: none;">
                        No hay horarios disponibles para la fecha y veterinario seleccionados.
                    </div>
                    @error('date') {{-- Este error es para el campo 'date' que ahora contiene la fecha y hora completa --}}
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
{{-- Es crucial incluir Axios para hacer las peticiones AJAX --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Define la URL de la ruta aquí, usando la función route() de Laravel
    // Esto asegura que la URL sea la correcta según tus rutas de Laravel
    const availableSlotsUrl = "{{ route('client.citas.get-available-slots') }}";

    document.addEventListener('DOMContentLoaded', function() {
        const veterinarianSelect = document.getElementById('veterinarian_id');
        const dateInput = document.getElementById('date_only_picker');
        const serviceSelect = document.getElementById('service_id');
        const timeSlotsContainer = document.getElementById('time-slots-container');
        const timeSlotSelect = document.getElementById('time_slot');
        const noSlotsMessage = document.getElementById('no-slots-message');

        function fetchAvailableTimeSlots() {
            const veterinarianId = veterinarianSelect.value;
            const selectedDate = dateInput.value;
            const serviceId = serviceSelect.value;

            // Solo hacemos la llamada AJAX si todos los campos requeridos tienen un valor
            if (veterinarianId && selectedDate && serviceId) {
                // Muestra el contenedor de slots y un mensaje de carga
                timeSlotsContainer.style.display = 'block';
                timeSlotSelect.innerHTML = '<option value="">Cargando horarios...</option>';
                noSlotsMessage.style.display = 'none'; // Oculta el mensaje de "no hay slots" mientras carga

                // Realiza la llamada AJAX usando Axios
                axios.get(availableSlotsUrl, { // <--- ¡AQUÍ ESTÁ EL CAMBIO CLAVE!
                    params: {
                        veterinarian_id: veterinarianId,
                        date: selectedDate,
                        service_id: serviceId
                    },
                    withCredentials: true
                })
                .then(response => {
                    // Limpia el select de slots y añade la opción por defecto
                    timeSlotSelect.innerHTML = '<option value="">Selecciona una hora</option>';
                    if (response.data.slots.length > 0) {
                        // Si hay slots disponibles, los añade al select
                        response.data.slots.forEach(slot => {
                            const option = document.createElement('option');
                            // El valor del option será la fecha completa (ej. "2025-06-25 09:00")
                            option.value = selectedDate + ' ' + slot.start;
                            // El texto visible será el rango de hora (ej. "09:00 - 09:30")
                            option.textContent = `${slot.start} - ${slot.end}`;
                            timeSlotSelect.appendChild(option);
                        });
                        noSlotsMessage.style.display = 'none'; // Asegura que el mensaje de "no slots" esté oculto
                    } else {
                        // Si no hay slots, muestra el mensaje correspondiente
                        noSlotsMessage.style.display = 'block';
                    }
                    console.log("URL de la petición realizada:", availableSlotsUrl); // Para depuración
                    console.log("Respuesta de los slots:", response.data.slots); // Para depuración
                })
                .catch(error => {
                    console.error('Error fetching available slots:', error);
                    console.log("URL de la petición fallida:", availableSlotsUrl); // Para depuración
                    if (error.response) {
                        console.error('Data del error:', error.response.data);
                        console.error('Status del error:', error.response.status);
                        console.error('Headers del error:', error.response.headers);
                    } else if (error.request) {
                        console.error('No se recibió respuesta del servidor:', error.request);
                    } else {
                        console.error('Error al configurar la petición:', error.message);
                    }
                    timeSlotSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
                    noSlotsMessage.style.display = 'none';
                    // Aquí podrías mostrar un mensaje de error más amigable al usuario en la interfaz
                });
            } else {
                // Si faltan campos, oculta el contenedor de slots y resetea el select
                timeSlotsContainer.style.display = 'none';
                timeSlotSelect.innerHTML = '<option value="">Selecciona un veterinario, servicio y fecha primero</option>';
                noSlotsMessage.style.display = 'none';
            }
        }

        // Añadir "listeners" a los cambios en los selects y el input de fecha
        veterinarianSelect.addEventListener('change', fetchAvailableTimeSlots);
        dateInput.addEventListener('change', fetchAvailableTimeSlots);
        serviceSelect.addEventListener('change', fetchAvailableTimeSlots);

        // Llamar a la función al cargar la página si ya hay valores seleccionados (útil para `old()` data en caso de errores de validación)
        // Esto es importante para recargar los slots si el usuario vuelve a la página con datos de sesión o old()
        if (veterinarianSelect.value && dateInput.value && serviceSelect.value) {
            fetchAvailableTimeSlots();
        }
    });
</script>
@endpush