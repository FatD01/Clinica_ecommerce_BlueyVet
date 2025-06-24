<!-- @extends('layouts.app')

@section('content')
<div class="container my-5">
    <h2 class="mb-4">Proponer Nueva Fecha para la Cita</h2>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('reprogramming_requests.store') }}" method="POST" id="reprogramming-form">
        @csrf

        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        <input type="hidden" name="veterinarian_id" value="{{ $appointment->veterinarian_id }}">
        <input type="hidden" name="client_id" value="{{ $appointment->client_id }}">

        <div class="mb-3">
            <label for="date" class="form-label">Nueva Fecha</label>
            <input type="date" name="date" id="date" class="form-control" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
        </div>

        <div class="mb-3">
            <label for="start_time" class="form-label">Hora Disponible</label>
            <select name="start_time" id="start_time" class="form-select" required>
                <option value="">Selecciona primero una fecha</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="reprogramming_reason" class="form-label">Motivo</label>
            <textarea name="reprogramming_reason" id="reprogramming_reason" class="form-control" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Proponer Reprogramación</button>
        <a href="{{ route('client.citas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('start_time');

    dateInput.addEventListener('change', function () {
        const selectedDate = this.value;
        const vetId = {{ $appointment->veterinarian_id }};
        const serviceId = {{ $appointment->service_id }};

        if (!selectedDate) return;

        fetch(`/horarios-disponibles?veterinarian_id=${vetId}&date=${selectedDate}&service_id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                timeSelect.innerHTML = '';

                if (data.slots.length === 0) {
                    const opt = document.createElement('option');
                    opt.textContent = 'No hay horarios disponibles';
                    timeSelect.appendChild(opt);
                    return;
                }

                const defaultOpt = document.createElement('option');
                defaultOpt.textContent = 'Selecciona una hora';
                defaultOpt.value = '';
                timeSelect.appendChild(defaultOpt);

                data.slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.full_datetime;
                    option.textContent = `${slot.start} - ${slot.end}`;
                    timeSelect.appendChild(option);
                });
            })
            .catch(err => {
                console.error(err);
                timeSelect.innerHTML = '<option value="">Error al obtener horarios</option>';
            });
    });
});
</script>
@endpush -->
