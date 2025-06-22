@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-4xl" x-data="{ active: null }">
    <!-- Encabezado mejorado -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4 text-bluey-dark">Preguntas Frecuentes</h1>
        <div class="w-24 h-1.5 bg-bluey-primary mx-auto mb-6"></div>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Hemos reunido las preguntas más frecuentes sobre el bienestar animal y sus respuestas prácticas.
        </p>
    </div>

    @if($faqs->isEmpty())
    <div class="bg-bluey-light p-6 rounded-lg text-center">
        <p class="text-gray-600">No hay preguntas frecuentes disponibles en este momento.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($faqs as $index => $faq)
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <!-- Pregunta - Acordeón -->
            <button
                @click="active === {{ $index }} ? active = null : active = {{ $index }}"
                class="w-full px-6 py-3 bg-white hover:bg-bluey-light2 text-left flex justify-between items-center transition-colors duration-200"
                :class="{ 'bg-bluey-light2': active === {{ $index }} }">
                <h2 class="text-lg font-semibold text-bluey-dark flex items-center">
                    <svg class="w-4 h-4 mr-3 transform transition-transform duration-200"
                        :class="{ 'rotate-90': active === {{ $index }} }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    {{ $faq->title }}
                </h2>
                <!-- <p class="text-gray-600 text-sm mb-6">Publicado el: {{ $faq->published_at->format('d/m/Y') }}</p> -->
                <svg class="w-5 h-5 text-bluey-primary transform transition-transform duration-200"
                    :class="{ 'rotate-180': active === {{ $index }} }"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Respuesta - Contenido desplegable -->
            <div
                x-show="active === {{ $index }}"
                x-collapse
                class="px-6 pb-4 pt-2 bg-white border-t border-gray-100">
                 <p class="text-gray-600 text-sm mb-6">Publicado el: {{ $faq->published_at->format('d/m/Y') }}</p>
                <div class="prose max-w-none text-gray-700">
                    
                {!! $faq->content !!}
                </div>

                @if($faq->excerpt)
                <div class="mt-4 p-4 bg-bluey-light rounded-lg">
                    <p class="font-medium text-bluey-dark">Resumen:</p>
                    <p class="text-gray-600">{{ $faq->excerpt }}</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection