{{-- resources/views/client/servicios/index.blade.php --}}

@extends('layouts.app')

@section('content')
{{-- HERO SECTION CON IMAGEN DE FONDO --}}
<section class="relative py-20 px-6 overflow-hidden bg-bluey-dark">
    {{-- Imagen de fondo con overlay --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('img/bg-hero-pets.jpg') }}" alt="Mascotas felices" class="w-full h-full object-cover object-center">
        <div class="absolute inset-0 bg-bluey-dark opacity-70"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-bluey-primary/30 to-bluey-secondary/30"></div>
    </div>

    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between relative z-10">
        <div class="w-full md:w-1/2 text-center md:text-left mb-10 md:mb-0">
            <h1 class="text-4xl lg:text-5xl font-bold mb-6 text-white leading-tight">
                Cuidado Excepcional<br>para tu Compañero <span class="text-bluey-light-yellow">Favorito</span>
            </h1>
            <p class="text-xl text-white mb-8 max-w-lg">
                En BlueyVet, combinamos medicina de vanguardia con compasión para ofrecer los mejores servicios veterinarios.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                <a href="{{ route('client.citas.create') }}"
                    class="bg-bluey-light-yellow hover:bg-bluey-gold-yellow text-bluey-dark font-bold py-3 px-8 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Agendar Cita
                </a>
                <a href="#servicios"
                    class=" border-2 border-white hover:bg-gray-300 text-bluey-light hover:text-bluey-dark  font-bold py-3 px-8 rounded-lg transition-all duration-300">
                    Nuestros Servicios
                </a>
            </div>
        </div>
        <div class="w-full md:w-1/4 mt-10 md:mt-0 flex justify-center">
            <div class="relative">
                <div class="absolute -bottom-4 -right-4 w-full h-full rounded-xl z-0"></div>
            </div>
        </div>
    </div>
</section>

{{-- SERVICES SECTION --}}
<section id="servicios" class="py-16 px-6 bg-gray-200">
    <div class="container mx-auto">
        <div class="text-center mb-16">
            <span class="text-bluey-secondary font-bold uppercase tracking-wider">Servicios Especializados</span>
            <h2 class="text-4xl font-bold text-bluey-dark mt-4 mb-6">Cuidado Integral para tu Mascota</h2>
            <div class="w-24 h-1 bg-bluey-primary mx-auto"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($servicios as $service)
            @php
                // Determina si el servicio está disponible o "próximamente"
                $isAvailable = $service->hasAvailableVeterinarians();
                $cardClasses = $isAvailable ? '' : 'opacity-60 grayscale cursor-not-allowed'; // Clases para "grisear" y deshabilitar el cursor
                $buttonClasses = $isAvailable ? 'bg-bluey-primary hover:bg-bluey-dark text-white' : 'bg-gray-400 text-gray-700 cursor-not-allowed'; // Clases para los botones
                $agendaButtonClasses = $isAvailable ? 'border-2 border-bluey-primary text-bluey-primary hover:bg-bluey-primary hover:text-white' : 'border-2 border-gray-400 text-gray-700 cursor-not-allowed'; // Clases para el botón de agendar
            @endphp

            
            <div class="group relative bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl {{ $cardClasses }}">
                {{-- Ribbon for featured services (optional) --}}
                @if($service->is_featured && $isAvailable) {{-- Solo si está disponible y es destacado --}}
                <div class="absolute top-0 right-0 bg-bluey-secondary text-white px-4 py-1 text-sm font-bold transform rotate-12 translate-x-2 -translate-y-1 z-10 shadow-md">
                    ¡Popular!
                </div>
                @endif

                {{-- Service Image --}}
                <div class="h-48 overflow-hidden">
                    <img src="{{ $service->image_url ?? asset('img/service-default.jpg') }}" alt="{{ $service->name }}" class="w-full h-full object-cover transition-transform duration-500 {{ $isAvailable ? 'group-hover:scale-110' : '' }}">
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-2xl font-bold text-bluey-dark">{{ $service->name }}</h3>
                        <span class="text-bluey-secondary text-xl font-bold">S/{{ number_format($service->price, 2) }}</span>
                    </div>

                    <p class="text-bluey-dark mb-5">{{ $service->description }}</p>

                    @if ($service->duration_minutes)
                    <div class="flex items-center text-bluey-dark mb-5">
                        <svg class="w-5 h-5 mr-2 text-bluey-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $service->duration_minutes }} minutos</span>
                    </div>
                    @endif

                    @if ($isAvailable)
                    <div class="flex flex-col space-y-3">
                        {{-- Botón Comprar Ahora --}}
                        <form action="{{ route('payments.purchase_service') }}" method="POST">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <button type="submit" class="w-full font-bold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-[1.02] shadow-md {{ $buttonClasses }}">
                                Comprar Ahora
                            </button>
                        </form>

                        {{-- Botón Agendar Cita --}}
                        <a href="{{ route('client.citas.create', ['preselected_service_id_from_purchase' => $service->id]) }}"
                            class="w-full font-bold py-3 px-6 rounded-lg transition-all duration-300 text-center {{ $agendaButtonClasses }}">
                            Agendar Cita
                        </a>

                        {{-- NUEVO: Botón "Ver Detalles" --}}
                        <a href="{{ route('client.servicios.show', $service->id) }}"
                           class="w-full text-center font-semibold py-2 rounded-lg transition-all duration-300 mt-2 
                                  text-bluey-primary hover:text-bluey-dark hover:bg-gray-200
                                  border border-white hover:border-transparent">
                            Ver Detalles
                        </a>
                    </div>
                    @else
                    <div class="bg-gray-300 text-gray-700 text-center py-3 px-6 rounded-lg font-bold">
                        Servicio Próximamente Disponible
                        <p class="text-sm mt-1">Aún no hay veterinarios asociados a esta especialidad.</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA SECTION --}}
<section class="py-16 px-6 bg-bluey-dark text-white">
    <div class="container mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6 text-bluey-light">¿No encuentras lo que necesitas?</h2>
        <p class="text-xl text-bluey-gold-yellow mb-8 max-w-2xl mx-auto">
            Contáctanos para servicios especializados o emergencias. Nuestro equipo está listo para ayudarte.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('contact.us') }}" {{-- Cambiado de '#' a la ruta nombrada --}}
                class="bg-bluey-secondary hover:bg-bluey-secondary-light text-white font-bold py-3 px-8 rounded-lg transition-all duration-300">
                Contáctanos
            </a>
            <a href="tel:+51944280482"
                class="border-2 border-white hover:bg-white hover:text-bluey-dark font-bold py-3 px-8 rounded-lg transition-all duration-300">
                Llamar Ahora
            </a>
        </div>
    </div>
</section>
@endsection