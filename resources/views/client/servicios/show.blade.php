@extends('layouts.app') {{-- Asume que tienes un layout principal llamado 'app' --}}

@section('title', $service->name . ' - Detalles del Servicio')

@section('content')
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-500 mb-6">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="{{ url('/') }}" class="text-bluey-dark hover:text-bluey-primary">Inicio</a>
                    <svg class="fill-current w-3 h-3 mx-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 72.324c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
                </li>
                <li class="flex items-center">
                    <a href="{{ route('client.servicios.index') }}" class="text-bluey-dark hover:text-bluey-primary">Servicios</a>
                    <svg class="fill-current w-3 h-3 mx-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 72.324c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
                </li>
                <li>
                    <span class="text-gray-700">{{ $service->name }}</span>
                </li>
            </ol>
        </nav>

        <div class="bg-white shadow-lg rounded-lg p-8 md:p-12">
            <h1 class="text-4xl font-bold text-bluey-dark mb-6">{{ $service->name }}</h1>

            {{-- Imagen del servicio - Usando image_url como en tu index --}}
            <img src="{{ $service->image_url ?? asset('img/service-default.jpg') }}" alt="{{ $service->name }}" class="w-full h-80 object-cover rounded-md mb-8 shadow-md">

            <div class="text-lg text-gray-700 leading-relaxed mb-8">
                <p>{{ $service->description }}</p>
                @if($service->long_description)
                    <div class="mt-4 prose max-w-none">
                        {!! $service->long_description !!} {{-- Usa {!! !!} si el contenido es HTML --}}
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between mt-8 flex-wrap gap-4">
                {{-- Precio del servicio --}}
                @if($service->price)
                    <span class="text-3xl font-extrabold text-bluey-primary">S/ {{ number_format($service->price, 2) }}</span>
                @endif
                
                {{-- Duración del servicio --}}
                @if ($service->duration_minutes)
                    <div class="flex items-center text-bluey-dark">
                        <svg class="w-6 h-6 mr-2 text-bluey-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xl font-semibold">{{ $service->duration_minutes }} minutos</span>
                    </div>
                @endif
            </div>

            {{-- Botones de acción: Comprar Ahora y Agendar Cita (replicando la lógica del index) --}}
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                @php
                    // Asegúrate de que este método 'hasAvailableVeterinarians()' exista en tu modelo Service
                    // Si no existe, deberás implementarlo o quitar esta condición
                    $isAvailable = $service->hasAvailableVeterinarians();
                    $buttonClasses = $isAvailable ? 'bg-bluey-primary hover:bg-bluey-dark text-white' : 'bg-gray-400 text-gray-700 cursor-not-allowed';
                    $agendaButtonClasses = $isAvailable ? 'border-2 border-bluey-primary text-bluey-primary hover:bg-bluey-primary hover:text-white' : 'border-2 border-gray-400 text-gray-700 cursor-not-allowed';
                @endphp

                @if ($isAvailable)
                    {{-- Botón Comprar Ahora (que, según tu lógica, inicia el proceso de pago y agenda) --}}
                    <form action="{{ route('payments.purchase_service') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">
                        <button type="submit" class="w-full font-bold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-[1.02] shadow-md {{ $buttonClasses }}">
                            Comprar Ahora
                        </button>
                    </form>

                    {{-- Botón Agendar Cita (si quieren ir directo a la agenda sin pasar por el pago de inmediato, como alternativa) --}}
                    <a href="{{ route('client.citas.create', ['preselected_service_id_from_purchase' => $service->id]) }}"
                        class="w-full sm:w-auto text-center font-bold py-3 px-6 rounded-lg transition-all duration-300 {{ $agendaButtonClasses }}">
                        Agendar Cita
                    </a>
                @else
                    <div class="bg-gray-300 text-gray-700 text-center py-3 px-6 rounded-lg font-bold w-full">
                        Servicio Próximamente Disponible
                        <p class="text-sm mt-1">Aún no hay veterinarios asociados a esta especialidad.</p>
                    </div>
                @endif
            </div>

            {{-- Puedes añadir más secciones aquí, como testimonios, preguntas frecuentes, etc. --}}
            <div class="mt-12 p-6 bg-gray-50 rounded-lg shadow-inner">
                <h3 class="text-2xl font-semibold text-bluey-dark mb-4">Información Adicional</h3>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    @if ($service->duration_minutes)
                        <li>Duración del servicio: aproximadamente <span class="font-bold">{{ $service->duration_minutes }} minutos</span>.</li>
                    @else
                        <li>Duración estimada: Varía según el tipo de servicio.</li>
                    @endif
                    
                    {{-- Horarios de atención --}}
                    <li>Horario de atención: Generalmente disponible durante el horario de la clínica. Los horarios exactos varían según el especialista y se confirman al agendar tu cita.</li>

                    {{-- Especialistas a cargo - AHORA USA $service->associated_veterinarians --}}
                    @if ($service->associated_veterinarians->isNotEmpty())
                        <li>Especialistas a cargo:
                            <ul class="list-disc list-inside ml-4 mt-1">
                                @foreach($service->associated_veterinarians as $vet)
                                    <li>
                                        <span class="font-bold">{{ $vet->user->name ?? 'Nombre no disponible' }}</span> 
                                        (Especialidad: {{ $vet->getSpecialtyNamesAttribute() ?? 'No especificada' }})
                                        {{-- Usamos getSpecialtyNamesAttribute() porque es un método de tu modelo Veterinarian que concatena las especialidades --}}
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li>Especialistas a cargo: No hay veterinarios asociados directamente a este servicio aún.</li>
                    @endif
                </ul>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('client.servicios.index') }}" class="text-bluey-primary hover:text-bluey-dark font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver a todos los servicios
                </a>
            </div>
        </div>
    </div>
</section>
@endsection