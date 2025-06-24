@extends('layouts.app') 

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
                                    data-duration="{{ $service->duration_minutes ?? 30 }}"
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
                    <select class="form-select @error('veterinarian_id') is-invalid @enderror" id="veterinarian_id" name="veterinarian_id" required disabled>
                        <option value="">Selecciona un servicio primero</option>
                        {{-- Las opciones de veterinario se cargarán dinámicamente con JavaScript --}}
                    </select>
                    @error('veterinarian_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo para la FECHA (separado de la hora) --}}
                <div class="mb-3">
                    <label for="date_only_picker" class="form-label">Fecha de la Cita</label>
                    <input type="text" class="form-control @error('date_only_picker') is-invalid @enderror" 
                                 id="date_only_picker" name="date_only_picker" 
                                 value="{{ old('date_only_picker', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                                 required readonly>
                    @error('date_only_picker')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contenedor para los slots de tiempo disponibles. Inicialmente oculto. --}}
                <div class="mb-3" id="time-slots-container" style="display: none;">
                    <label for="time_slot" class="form-label">Hora de Inicio Disponible</label>
                    <select class="form-select @error('date') is-invalid @enderror" id="time_slot" name="date" required>
                        <option value="">Selecciona una hora</option>
                        {{-- Los slots se cargarán aquí con JavaScript --}}
                    </select>
                    <div id="no-slots-message" class="text-info mt-2" style="display: none;">
                        No hay horarios disponibles para la fecha y veterinario seleccionados.
                    </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('service_id');
            const veterinarianSelect = document.getElementById('veterinarian_id');
            const dateInput = document.getElementById('date_only_picker');
            const timeSlotsContainer = document.getElementById('time-slots-container');
            const timeSlotSelect = document.getElementById('time_slot');
            const noSlotsMessage = document.getElementById('no-slots-message');

            const getVeterinariansByServiceUrl = "{{ route('client.citas.get-veterinarians-by-service') }}";
            const availableSlotsUrl = "{{ route('client.citas.get-available-slots') }}";
            const getVeterinarianWorkingDaysUrl = "{{ route('client.veterinarians.working-days') }}";

            let flatpickrInstance; // Variable para almacenar la instancia de Flatpickr

            // Función para inicializar/actualizar Flatpickr
            // Ahora acepta un array de reglas para 'enable'
            function initializeFlatpickr(enableRules = []) {
                if (flatpickrInstance) {
                    flatpickrInstance.destroy(); // Destruir la instancia existente si la hay
                }

                // Calcular maxDate: hoy + 30 días
                const today = new Date();
                const maxDate = new Date();
                maxDate.setDate(today.getDate() + 30); // Limite a 30 días en el futuro (1 mes aprox.)

                flatpickrInstance = flatpickr(dateInput, {
                    locale: "es", // Establecer idioma a español
                    dateFormat: "Y-m-d", // Formato de fecha para el input
                    minDate: "today", // No permitir fechas pasadas
                    maxDate: maxDate, // Limitar a 30 días en el futuro
                    // Usamos 'enable' para especificar qué fechas están disponibles
                    // y deshabilitar implícitamente todo lo demás.
                    enable: enableRules, 
                    onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            dateInput.dispatchEvent(new Event('change'));
                        }
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            dateInput.value = dateStr;
                            fetchAvailableTimeSlots(); // Recargar slots al cambiar la fecha
                        }
                    }
                });
            }

            // Inicializar Flatpickr la primera vez con configuraciones por defecto
            // Inicialmente, no habilitamos nada para que el calendario esté "limpio" hasta que se seleccione un veterinario
            initializeFlatpickr([]); // Pasa un array vacío para que el calendario inicie con todos los días deshabilitados

            // Función para obtener los días de trabajo del veterinario
            function fetchVeterinarianWorkingDays(veterinarianId) {
                if (!veterinarianId) {
                    // Si no hay veterinario, resetea Flatpickr para deshabilitar todo
                    initializeFlatpickr([]); // Pasa un array vacío a 'enable', deshabilitando todo
                    return;
                }

                axios.post(getVeterinarianWorkingDaysUrl, {
                        veterinarian_id: veterinarianId
                    }, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        // LA CLAVE AQUÍ ES response.data.workingDays (nota la capitalización 'D')
                        const workingDaysInSpanish = response.data.workingDays; 
                        const enableRules = [];

                        if (Array.isArray(workingDaysInSpanish)) {
                            if (workingDaysInSpanish.length > 0) {
                                // Mapear nombres de días en español a índices numéricos de Flatpickr (0=Dom, 1=Lun, etc.)
                                const dayNameToFlatpickrIndex = {
                                    'domingo': 0,
                                    'lunes': 1,
                                    'martes': 2,
                                    'miércoles': 3,
                                    'jueves': 4,
                                    'viernes': 5,
                                    'sábado': 6
                                };
                                
                                // Añadir una función a 'enable' para habilitar solo esos días de la semana
                                enableRules.push(function(date) {
                                    // Obtener el nombre del día de la semana actual en español (basado en el índice)
                                    // Y luego ver si está en la lista de workingDaysInSpanish.
                                    const currentDayIndex = date.getDay(); // 0-6
                                    const currentDayNameInSpanish = Object.keys(dayNameToFlatpickrIndex).find(
                                        key => dayNameToFlatpickrIndex[key] === currentDayIndex
                                    );

                                    return workingDaysInSpanish.includes(currentDayNameInSpanish);
                                });

                            } else {
                                console.log('Array de workingDays está vacío.');
                            }
                        } else {
                            console.warn("Días de trabajo del veterinario no es un array. Calendario se inicializará sin días habilitados.");
                        }
                        
                        // Inicializar Flatpickr con las reglas de habilitación obtenidas
                        initializeFlatpickr(enableRules);
                        
                        // Después de actualizar los días laborables, intenta cargar los slots
                        fetchAvailableTimeSlots();

                    })
                    .catch(error => {
                        console.error('Error fetching veterinarian working days:', error);
                        // En caso de error, resetea Flatpickr para deshabilitar todo
                        initializeFlatpickr([]);
                        fetchAvailableTimeSlots();
                    });
            }


            // Función para cargar veterinarios por servicio
            function loadVeterinariansByService(serviceId) {
                veterinarianSelect.innerHTML = '<option value="">Cargando veterinarios...</option>';
                veterinarianSelect.disabled = true;
                // Resetea el calendario al cambiar el servicio (hasta que se seleccione un veterinario)
                initializeFlatpickr([]);

                if (!serviceId) {
                    veterinarianSelect.innerHTML = '<option value="">Selecciona un servicio primero</option>';
                    return;
                }

                axios.get(getVeterinariansByServiceUrl, {
                        params: {
                            service_id: serviceId
                        }
                    })
                    .then(response => {
                        veterinarianSelect.innerHTML = '<option value="">Selecciona un veterinario</option>';
                        if (response.data.length > 0) {
                            response.data.forEach(vet => {
                                const option = document.createElement('option');
                                option.value = vet.id;
                                option.textContent = `${vet.name} (${vet.specialties})`;
                                veterinarianSelect.appendChild(option);
                            });
                            veterinarianSelect.disabled = false;
                            
                            // Intenta seleccionar el veterinario que ya estaba seleccionado (si viene de old())
                            const oldVetId = "{{ old('veterinarian_id') }}";
                            if (oldVetId) {
                                veterinarianSelect.value = oldVetId;
                                fetchVeterinarianWorkingDays(oldVetId);
                            } else {
                                // Si no hay veterinario preseleccionado, el calendario debe estar "vacío"
                                initializeFlatpickr([]);
                            }

                        } else {
                            veterinarianSelect.innerHTML = '<option value="">No hay veterinarios para este servicio</option>';
                            initializeFlatpickr([]); // Si no hay veterinarios, deshabilita todo
                            timeSlotsContainer.style.display = 'none';
                            timeSlotSelect.innerHTML = '<option value="">Selecciona un veterinario, servicio y fecha primero</option>';
                            noSlotsMessage.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching veterinarians:', error);
                        veterinarianSelect.innerHTML = '<option value="">Error al cargar veterinarios</option>';
                        veterinarianSelect.disabled = true;
                        initializeFlatpickr([]); // Si hay error, deshabilita todo
                        timeSlotsContainer.style.display = 'none';
                        timeSlotSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
                        noSlotsMessage.style.display = 'none';
                    });
            }

            // Función para cargar horarios disponibles
            function fetchAvailableTimeSlots() {
                const veterinarianId = veterinarianSelect.value;
                const selectedDate = dateInput.value;
                const serviceId = serviceSelect.value;

                timeSlotsContainer.style.display = 'block';
                timeSlotSelect.innerHTML = '<option value="">Cargando horarios...</option>';
                noSlotsMessage.style.display = 'none';

                if (veterinarianId && selectedDate && serviceId) {
                    const params = new URLSearchParams({
                        veterinarian_id: veterinarianId,
                        date: selectedDate,
                        service_id: serviceId
                    }).toString();

                    fetch(`${availableSlotsUrl}?${params}`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errorData => {
                                    throw new Error(errorData.message || 'Error al cargar horarios.');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            timeSlotSelect.innerHTML = '<option value="">Selecciona una hora</option>';
                            if (data.slots && data.slots.length > 0) {
                                data.slots.forEach(slot => {
                                    const option = document.createElement('option');
                                    option.value = selectedDate + ' ' + slot.start;
                                    option.textContent = `${slot.start} - ${slot.end}`;
                                    timeSlotSelect.appendChild(option);
                                });
                                noSlotsMessage.style.display = 'none';
                            } else {
                                noSlotsMessage.style.display = 'block';
                            }
                            const oldTimeSlot = "{{ old('date') }}";
                            if (oldTimeSlot) {
                                const optionToSelect = Array.from(timeSlotSelect.options).find(option => option.value === oldTimeSlot);
                                if (optionToSelect) {
                                    optionToSelect.selected = true;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching available slots:', error);
                            timeSlotSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
                            noSlotsMessage.style.display = 'none';
                            alert('Hubo un error al cargar los horarios disponibles. Por favor, intente de nuevo.');
                        });
                } else {
                    timeSlotsContainer.style.display = 'none';
                    timeSlotSelect.innerHTML = '<option value="">Selecciona un veterinario, servicio y fecha primero</option>';
                    noSlotsMessage.style.display = 'none';
                }
            }


            // --- Event listeners ---
            serviceSelect.addEventListener('change', function() {
                const selectedServiceId = this.value;
                loadVeterinariansByService(selectedServiceId);
                timeSlotsContainer.style.display = 'none';
                timeSlotSelect.innerHTML = '<option value="">Selecciona un veterinario, servicio y fecha primero</option>';
                noSlotsMessage.style.display = 'none';
            });

            veterinarianSelect.addEventListener('change', function() {
                const selectedVetId = this.value;
                fetchVeterinarianWorkingDays(selectedVetId);
                timeSlotsContainer.style.display = 'none';
                timeSlotSelect.innerHTML = '<option value="">Selecciona una hora</option>';
                noSlotsMessage.style.display = 'none';
            });

            // --- Cargar datos iniciales si existen (para 'old()' values) ---
            const initialServiceId = serviceSelect.value;
            if (initialServiceId) {
                loadVeterinariansByService(initialServiceId);
            }
            const oldVetId = "{{ old('veterinarian_id') }}";
            const oldDate = "{{ old('date_only_picker') }}";
            if (initialServiceId && oldVetId && oldDate) {
                 // Si hay un servicio, veterinario y fecha preseleccionados,
                 // asegúrate de que el Flatpickr se inicialice con los días correctos
                 // y luego los slots.
                fetchVeterinarianWorkingDays(oldVetId);
            } else {
                // Si al inicio no hay un veterinario seleccionado o servicio, 
                // aseguramos que el calendario esté deshabilitado por completo por defecto.
                initializeFlatpickr([]);
            }
        });
    </script>
@endpush