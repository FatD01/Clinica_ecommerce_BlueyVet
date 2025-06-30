@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-6 p-6 bg-white shadow-lg rounded-lg">
    <a href="{{ route('client.mascotas.index') }}" class="inline-block mb-6 text-bluey-primary hover:text-bluey-dark">
        &larr; Volver a Mis Mascotas
    </a>

    <h2 class="text-3xl font-bold text-bluey-dark mb-4">Detalles de {{ $mascota->name }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            @if($mascota->hasMedia('avatars'))
                <img src="{{ $mascota->getFirstMediaUrl('avatars') }}" alt="Avatar de {{ $mascota->name }}" class="w-48 h-48 object-cover rounded-full mx-auto mb-4 border-2 border-bluey-light">
            @else
                <img src="https://via.placeholder.com/150?text=No+Avatar" alt="No Avatar" class="w-48 h-48 object-cover rounded-full mx-auto mb-4 border-2 border-gray-300">
            @endif

            <p class="text-lg font-semibold text-bluey-dark">Especie: <span class="font-normal text-gray-700">{{ $mascota->species }}</span></p>
            @if($mascota->race)
                <p class="text-lg font-semibold text-bluey-dark">Raza: <span class="font-normal text-gray-700">{{ $mascota->race }}</span></p>
            @endif
            @if($mascota->weight)
                <p class="text-lg font-semibold text-bluey-dark">Peso: <span class="font-normal text-gray-700">{{ $mascota->weight }} kg</span></p>
            @endif
            @if($mascota->birth_date)
                <p class="text-lg font-semibold text-bluey-dark">Fecha de Nacimiento: <span class="font-normal text-gray-700">{{ \Carbon\Carbon::parse($mascota->birth_date)->format('d/m/Y') }}</span></p>
            @endif
            @if($mascota->allergies)
                <p class="text-lg font-semibold text-bluey-dark">Alergias: <span class="font-normal text-gray-700">{{ $mascota->allergies }}</span></p>
            @endif
        </div>

        {{-- SECCIÓN DE RECORDATORIOS --}}
        <div id="reminders" class="mt-8 md:mt-0"> {{-- ¡Este es el ID al que apunta el enlace! --}}
            <h3 class="text-2xl font-bold text-bluey-dark mb-4">Recordatorios de {{ $mascota->name }}</h3>

            @if($mascota->reminders->count() > 0)
                <div class="space-y-4">
                    @foreach($mascota->reminders->sortByDesc('remind_at') as $reminder)
                        @php
                            $isExpired = \Carbon\Carbon::now()->isAfter($reminder->remind_at);
                            $borderColor = $isExpired ? 'border-red-400' : 'border-bluey-primary';
                            $statusText = $isExpired ? 'Expira el' : 'Programado para';
                            $textColor = $isExpired ? 'text-red-600' : 'text-green-600';
                        @endphp
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm border-l-4 {{ $borderColor }}">
                            <p class="font-semibold text-lg text-bluey-dark">{{ $reminder->title }}</p>
                            <p class="text-sm text-gray-700 mt-1">{{ $reminder->description }}</p>
                            <p class="text-xs {{ $textColor }} mt-2">
                                {{ $statusText }}: {{ $reminder->remind_at->format('d/m/Y H:i A') }}
                            </p>
                            {{-- Aquí puedes añadir más acciones si lo deseas, como marcar como completado, editar, etc. --}}
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No hay recordatorios programados para esta mascota.</p>
            @endif
        </div>
    </div>
</div>
@endsection