<!-- @extends('layouts.app') {{-- O tu layout principal --}}

@section('content')
    <div class="container my-4">
        <a href="{{ route('client.citas.index') }}" class="btn btn-outline-secondary mb-3 ">
            <i class="fas fa-arrow-left me-2 "></i> Volver a Mis Citas
        </a>
        
        <h2 class="mb-4">Detalles de la Cita #{{ $appointment->id }}</h2>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-paw me-2"></i> Mascota: {{ $appointment->mascota->name }}</h5>
                <p class="card-text mb-2"><strong><i class="fas fa-calendar-alt me-2"></i>Fecha y Hora:</strong> {{ $appointment->date->format('d/m/Y H:i') }}</p>
                <p class="card-text mb-2"><strong><i class="fas fa-concierge-bell me-2"></i>Servicio:</strong> {{ $appointment->service->name }}</p>
                <p class="card-text mb-2"><strong><i class="fas fa-user-md me-2"></i>Veterinario:</strong> {{ $appointment->veterinarian->user->name }}</p>
                <p class="card-text mb-2"><strong><i class="fas fa-comment-dots me-2"></i>Motivo de Consulta:</strong> {{ $appointment->reason }}</p>
                <p class="card-text mb-2">
                    <strong><i class="fas fa-info-circle me-2"></i>Estado:</strong> 
                    @php
                        $statusClass = '';
                        switch (strtolower($appointment->status)) {
                            case 'pending': $statusClass = 'bg-warning text-dark'; break;
                            case 'confirmed': $statusClass = 'bg-primary'; break;
                            case 'completed': $statusClass = 'bg-success'; break;
                            case 'cancelled': $statusClass = 'bg-danger'; break;
                            default: $statusClass = 'bg-secondary'; break;
                        }
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                </p>
                {{-- Aquí puedes añadir el costo si el Appointment tuviera un campo de costo --}}
                @if(isset($appointment->amount) && isset($appointment->currency))
                    <p class="card-text mb-2"><strong><i class="fas fa-dollar-sign me-2"></i>Costo:</strong> {{ number_format($appointment->amount, 2) }} {{ $appointment->currency }}</p>
                @elseif($appointment->serviceOrder && $appointment->serviceOrder->amount)
                    {{-- Si el costo está en la ServiceOrder vinculada --}}
                    <p class="card-text mb-2"><strong><i class="fas fa-dollar-sign me-2"></i>Costo (Orden de Servicio):</strong> {{ number_format($appointment->serviceOrder->amount, 2) }} {{ $appointment->serviceOrder->currency }}</p>
                @endif
                <p class="card-text mb-2"><strong><i class="fas fa-id-card me-2"></i>ID Orden Local:</strong> {{ $appointment->id }}</p>
                @if($appointment->service_order_id)
                    <p class="card-text mb-2"><strong><i class="fas fa-receipt me-2"></i>Orden de Servicio #</strong> {{ $appointment->service_order_id }}</p>
                @endif
                
                <hr>
                <div class="text-end">
                    {{-- Puedes añadir más botones de acción aquí si los necesitas --}}
                </div>
            </div>
        </div>
    </div>
@endsection -->