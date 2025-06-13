{{-- resources/views/client/servicios/index.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold text-center text-primary mb-10">Nuestros Servicios Veterinarios</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($servicios as $service)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform transform hover:scale-105
                            {{ $service->status == 'desactivado' ? 'opacity-60 grayscale cursor-not-allowed' : '' }}">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold text-text mb-2">{{ $service->name }}</h2>
                        <p class="text-light-text mb-4">{{ $service->description }}</p>

                        @if ($service->duration_minutes)
                            <p class="text-light-text mb-2 text-md">
                                <span class="font-semibold">Duración:</span> {{ $service->duration_minutes }} minutos
                            </p>
                        @endif

                        <div class="flex justify-between items-center mb-4">
                            <span class="text-xl font-bold text-secondary">S/{{ number_format($service->price, 2) }}</span>
                            <img src="{{ asset('images/bluey_icon.png') }}" alt="Bluey Icon" class="h-8 w-8">
                        </div>

                        @if ($service->status != 'desactivado')
                            {{-- Formulario para "Comprar Servicio Directamente" --}}
                            {{-- Esto envía una solicitud POST a PaymentController@purchaseService, que crea la ServiceOrder y luego redirige a la página de pago --}}
                            <form action="{{ route('payments.purchase_service') }}" method="POST" class="mb-2">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <button type="submit" class="w-full bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-800 transition duration-300">
                                    Comprar Servicio Ahora
                                </button>
                            </form>

                            {{-- Botón para ir a agendar una cita con este servicio --}}
                            {{-- Esto simplemente va a la página de creación de citas, preseleccionando el servicio --}}
                            <a href="{{ route('client.citas.create', ['preselected_service_id_from_purchase' => $service->id]) }}"
                                class="w-full border border-primary text-primary font-bold py-3 px-6 rounded-lg hover:bg-primary hover:text-white transition duration-300 block text-center">
                                Agendar Cita con este Servicio
                            </a>
                        @else
                            <button type="button" class="w-full bg-gray-400 text-white font-bold py-3 px-6 rounded-lg cursor-not-allowed" disabled>
                                Servicio Desactivado
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection