@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h2 class="mb-4">Reprogramar Cita</h2>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">Actualiza la fecha y hora de tu cita</div>
        <div class="card-body">
            <form action="{{ route('client.citas.update', $appointment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="veterinarian_id" value="{{ $appointment->veterinarian_id }}">
                <input type="hidden" name="service_id" value="{{ $preselectedService->id }}">

                {{-- Mascota --}}
                <div class="mb-3">
                    <label for="mascota_id" class="form-label">Mascota</label>
                    <select class="form-select" name="mascota_id" required>
                        @foreach($mascotas as $mascota)
                            <option value="{{ $mascota->id }}" {{ $appointment->mascota_id == $mascota->id ? 'selected' : '' }}>
                                {{ $mascota->name }} ({{ $mascota->species }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Fecha --}}
                <div class="mb-3">
                    <label for="date_only_picker" class="form-label">Nueva Fecha</label>
                    <input type="text" id="date_only_picker" class="form-control" value="{{ old('date_only_picker', $appointment->date->format('Y-m-d')) }}" required readonly>
                </div>

                {{-- Horas disponibles --}}
                <div class="mb-3" id="time-slots-container">
                    <label for="time_slot" class="form-label">Nueva Hora</label>
                    <select class="form-select" id="time_slot" name="date" required>
                        <option value="">Cargando horarios...</option>
                    </select>
                    <div id="no-slots-message" class="text-info mt-2" style="display: none;">
                        No hay horarios disponibles para la fecha seleccionada.
                    </div>
                </div>

                {{-- Motivo --}}
                <div class="mb-3">
                    <label for="reason" class="form-label">Motivo (opcional)</label>
                    <textarea name="reason" class="form-control" rows="3">{{ old('reason', $appointment->reason) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Reprogramar Cita</button>
                <a href="{{ route('client.citas.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('date_only_picker');
    const timeSlotSelect = document.getElementById('time_slot');
    const timeSlotsContainer = document.getElementById('time-slots-container');
    const noSlotsMessage = document.getElementById('no-slots-message');
    const veterinarianId = "{{ $appointment->veterinarian_id }}";
    const serviceId = "{{ $preselectedService->id }}";

    const workingDaysUrl = "{{ route('client.veterinarians.working-days') }}";
    const slotsUrl = "{{ route('client.citas.get-available-slots') }}";

    let flatpickrInstance;

    function initCalendar(allowedDays) {
        if (flatpickrInstance) flatpickrInstance.destroy();
        const enableRules = [function(date) {
            const dias = {
                'domingo': 0, 'lunes': 1, 'martes': 2, 'miércoles': 3,
                'jueves': 4, 'viernes': 5, 'sábado': 6
            };
            return allowedDays.some(d => dias[d.toLowerCase()] === date.getDay());
        }];
        flatpickrInstance = flatpickr(dateInput, {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: "today",
            maxDate: new Date().fp_incr(30),
            enable: enableRules,
            onChange: fetchTimeSlots
        });
    }

    function fetchWorkingDays() {
        axios.post(workingDaysUrl, {
            veterinarian_id: veterinarianId
        }, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(res => {
            initCalendar(res.data.workingDays || []);
        });
    }

    function fetchTimeSlots(selectedDates) {
        const selectedDate = selectedDates[0].toISOString().slice(0,10);
        timeSlotSelect.innerHTML = '<option value="">Cargando horarios...</option>';
        noSlotsMessage.style.display = 'none';

        const params = new URLSearchParams({
            veterinarian_id: veterinarianId,
            date: selectedDate,
            service_id: serviceId
        }).toString();

        fetch(`${slotsUrl}?${params}`)
            .then(res => res.json())
            .then(data => {
                timeSlotSelect.innerHTML = '<option value="">Selecciona una hora</option>';
                if (data.slots && data.slots.length > 0) {
                    data.slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = selectedDate + ' ' + slot.start;
                        option.textContent = `${slot.start} - ${slot.end}`;
                        timeSlotSelect.appendChild(option);
                    });
                } else {
                    noSlotsMessage.style.display = 'block';
                }
            });
    }

    fetchWorkingDays();
});
</script>
@endpush
