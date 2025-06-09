{{-- resources/views/client/servicios/index.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold text-center text-primary mb-10">Nuestros Servicios Veterinarios</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($servicios as $service)
                {{-- Clase condicional para grisado --}}
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
                            <img src="{{ asset('img/logo-blueyvet.png') }}" alt="Bluey Icon" class="h-8 w-8">
                        </div>

                        @if ($service->status != 'desactivado')
                            <form action="{{ route('payments.checkout') }}" method="POST"> {{-- ¡Esta es la ruta correcta! --}}
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <input type="hidden" name="amount" value="{{ $service->price }}">
                                <input type="hidden" name="service_name" value="{{ $service->name }}">

                                <button type="submit" class="w-full bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-800 transition duration-300">
                                    Reservar y Pagar Ahora
                                </button>
                            </form>
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