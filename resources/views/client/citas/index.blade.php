@extends('layouts.app')

@section('content')

<div class="container">
    <h1 class="page-title">
        <i class="fas fa-paw me-3"></i>
        Mis Citas Agendadas
        <i class="fas fa-paw ms-3"></i>
    </h1>

    {{-- Mensajes de éxito/info/error --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close btn-close-white float-end" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i> {{-- Icono para información --}}
        {{ session('info') }}
        <button type="button" class="btn-close btn-close-white float-end" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{-- Icono para error --}}
        {{ session('error') }}
        <button type="button" class="btn-close btn-close-white float-end" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('client.citas.create') }}" class="new-appointment-btn">
            <i class="fas fa-plus-circle me-2"></i>
            Agendar Nueva Cita
        </a>
    </div>

    @forelse($groupedAppointments as $mascotaId => $appointmentsForMascota)
    @php
    $mascota = $appointmentsForMascota->first()->mascota;
    $mascotaTypeIcon = (strtolower($mascota->species) === 'perro') ? 'fas fa-dog' : ((strtolower($mascota->species) === 'gato') ? 'fas fa-cat' : 'fas fa-paw');

    $mascotaAvatarPath = $mascota->getFirstMediaUrl('avatars', 'thumb');
    // $mascotaAvatarPath = $mascota->getFirstMediaUrl('avatars');

    if (empty($mascotaAvatarPath)) {
    $mascotaAvatarPath = asset('images/default_pet_avatar.png');
    }
    @endphp

    <div class="pet-card">
        <div class="pet-header">
            <div class="pet-info">
                <div class="pet-name-section">
                    <i class="{{ $mascotaTypeIcon }} pet-icon"></i>
                    <h2 class="pet-name">{{ $mascota->name }}</h2>
                    <i class="{{ $mascotaTypeIcon }} pet-icon"></i>
                </div>
                <img src="{{ $mascotaAvatarPath }}" alt="{{ $mascota->name }}" class="pet-avatar">
            </div>
        </div>

        <div class="appointments-grid">
            @foreach($appointmentsForMascota as $appointment)
            <div class="appointment-card">
                <div class="appointment-header">
                    <div class="appointment-date">
                        <i class="fas fa-calendar-alt"></i>
                        {{ $appointment->date->format('d/m/Y H:i') }}
                    </div>
                    @php
                    $statusClass = '';
                    switch (strtolower($appointment->status)) {
                    case 'pending': $statusClass = 'status-pending'; break;
                    case 'confirmed': $statusClass = 'status-confirmed'; break;
                    case 'completed': $statusClass = 'status-completed'; break;
                    case 'cancelled': $statusClass = 'status-cancelled'; break;
                    default: $statusClass = 'status-default'; break;
                    }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                </div>

                <div class="appointment-service">
                    <i class="{{ ($appointment->service->name == 'Grooming') ? 'fas fa-cut' : 'fas fa-stethoscope' }} me-2"></i>
                    {{ $appointment->service->name }}
                </div>

                <div class="appointment-details">
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="detail-text">
                            <div class="detail-label">Veterinario</div>
                            <div class="detail-value">{{ $appointment->veterinarian?->user?->name }}</div>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="detail-text">
                            <div class="detail-label">Motivo</div>
                            <div class="detail-value">{{ $appointment->reason }}</div>
                        </div>
                    </div>

                    @if(isset($appointment->amount) && isset($appointment->currency))
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="detail-text">
                            <div class="detail-label">Costo</div>
                            <div class="detail-value">{{ number_format($appointment->amount, 2) }} {{ $appointment->currency }}</div>
                        </div>
                    </div>
                    @elseif($appointment->serviceOrder && $appointment->serviceOrder->amount)
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="detail-text">
                            <div class="detail-label">Costo (Orden)</div>
                            <div class="detail-value">{{ number_format($appointment->serviceOrder->amount, 2) }} {{ $appointment->serviceOrder->currency }}</div>
                        </div>
                    </div>
                    @endif

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="detail-text">
                            <div class="detail-label">ID Cita</div>
                            <div class="detail-value">#{{ $appointment->id }}</div>
                        </div>
                    </div>
                </div>

                <div class="appointment-actions mt-3 text-center">
                    {{-- Botón de Reprogramar ahora llama --}}
                    @if($appointment->status === 'pending')
                    <a href="tel:+51944280482" class="btn btn-warning me-2">
                        <i class="fas fa-phone me-1"></i> Llamar para Reprogramar
                    </a>
                    @endif

                    {{-- Botón Ver Detalles Reprogramación si ya fue reprogramada --}}
                    @if($appointment->status === 'reprogrammed' && $appointment->reprogrammingRequests->where('status', 'applied')->isNotEmpty())
                    <a href="{{ route('client.citas.reprogram.status', $appointment->id) }}" class="btn btn-outline-success me-2">
                        <i class="fas fa-info-circle me-1"></i> Ver Detalles Reprogramación
                    </a>
                    @endif

                    {{-- Botón Ver Detalles (si no se muestra ya el de Ver Reprogramación) --}}
                    @if(!($appointment->status === 'reprogrammed' && $appointment->reprogrammingRequests->where('status', 'applied')->isNotEmpty()))
                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#appointmentDetailModal"
                        data-id="{{ $appointment->id }}"
                        data-mascota-name="{{ $appointment->mascota->name }}"
                        data-date="{{ $appointment->date->format('d/m/Y H:i') }}"
                        data-service-name="{{ $appointment->service->name }}"
                        data-veterinarian-name="{{ $appointment->veterinarian->user?->name ?? 'Sin asignar' }}"
                        data-reason="{{ $appointment->reason }}"
                        data-status="{{ ucfirst($appointment->status) }}"
                        data-amount="{{ isset($appointment->serviceOrder->amount) ? number_format($appointment->serviceOrder->amount, 2) : 'N/A' }}"
                        data-currency="{{ isset($appointment->serviceOrder->currency) ? $appointment->serviceOrder->currency : '' }}">
                        <i class="fas fa-eye me-1"></i> Ver Detalles
                    </button>
                    @endif
                </div>

            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center" role="alert">
            Aún no tienes citas agendadas para tus mascotas. ¡Agenda una ahora!
        </div>
    </div>
    @endforelse
</div>

{{-- MODAL DE DETALLES DE LA CITA --}}
<div class="modal fade" id="appointmentDetailModal" tabindex="-1" aria-labelledby="appointmentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header custom-modal-header">
                <h5 class="modal-title" id="appointmentDetailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Detalles de la Cita #<span id="modal-appointment-id"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body custom-modal-body">
                <p><strong><i class="fas fa-paw me-2"></i>Mascota: </strong> <span id="modal-mascota-name"></span></p>
                <p><strong><i class="fas fa-calendar-alt me-2"></i>Fecha y Hora: </strong> <span id="modal-appointment-date"></span></p>
                <p><strong><i class="fas fa-briefcase me-2"></i>Servicio: </strong> <span id="modal-service-name"></span></p>
                <p><strong><i class="fas fa-user-md me-2"></i>Veterinario: </strong> <span id="modal-veterinarian-name"></span></p>
                <p><strong><i class="fas fa-file-alt me-2"></i>Motivo de Consulta: </strong> <span id="modal-reason"></span></p>
                <p><strong><i class="fas fa-thermometer-half me-2"></i>Estado: </strong> <span id="modal-status"></span></p>
                <p class="mb-0"><strong><i class="fas fa-dollar-sign me-2"></i>Costo: </strong> <span id="modal-amount"></span> <span id="modal-currency"></span></p>
            </div>
            <div class="modal-footer custom-modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ... (Tu código existente para interactividad de tarjetas y alertas de sesión) ...

        // Lógica para llenar el modal con los datos de la cita
        const appointmentDetailModalElement = document.getElementById('appointmentDetailModal');

        if (appointmentDetailModalElement) {
            // No es necesario inicializar el modal con new bootstrap.Modal() si usas data-bs-toggle="modal"
            // Bootstrap lo inicializa automáticamente. Sin embargo, si lo necesitas para otras operaciones, puedes dejarlo.
            // let appointmentDetailModal = new bootstrap.Modal(appointmentDetailModalElement); // Mantener si lo necesitas programáticamente

            appointmentDetailModalElement.addEventListener('show.bs.modal', function(event) {
                console.log('Evento show.bs.modal disparado.'); // Para depuración
                const button = event.relatedTarget;

                // Extraer información de los atributos data-* del botón
                const appointmentId = button.getAttribute('data-id');
                const mascotaName = button.getAttribute('data-mascota-name');
                const appointmentDate = button.getAttribute('data-date');
                const serviceName = button.getAttribute('data-service-name');
                const veterinarianName = button.getAttribute('data-veterinarian-name');
                const reason = button.getAttribute('data-reason');
                const status = button.getAttribute('data-status');
                const amount = button.getAttribute('data-amount');
                const currency = button.getAttribute('data-currency');

                // Para depuración: Ver los datos que se están extrayendo
                console.log('Datos extraídos:', {
                    appointmentId,
                    mascotaName,
                    appointmentDate,
                    serviceName,
                    veterinarianName,
                    reason,
                    status,
                    amount,
                    currency
                });

                // Actualizar el contenido del modal
                // Usamos querySelector dentro del elemento del modal para asegurar que encontramos los IDs correctos
                appointmentDetailModalElement.querySelector('#modal-appointment-id').textContent = appointmentId || 'N/A';
                appointmentDetailModalElement.querySelector('#modal-mascota-name').textContent = mascotaName || 'N/A';
                appointmentDetailModalElement.querySelector('#modal-appointment-date').textContent = appointmentDate || 'N/A';
                appointmentDetailModalElement.querySelector('#modal-service-name').textContent = serviceName || 'N/A';
                appointmentDetailModalElement.querySelector('#modal-veterinarian-name').textContent = veterinarianName || 'N/A';
                appointmentDetailModalElement.querySelector('#modal-reason').textContent = reason || 'N/A';
                appointmentDetailModalElement.querySelector('#modal-status').textContent = status || 'N/A';
                appointmentDetailModalElement.querySelector('#modal-amount').textContent = amount || 'N/A';
                appointmentDetailModalElement.querySelector('#modal-currency').textContent = currency || '';
            });
        } else {
            console.error('El elemento del modal con ID #appointmentDetailModal no fue encontrado.');
        }
    });
</script>
@endsection