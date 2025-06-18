{{-- resources/views/faqs/index.blade.php --}}

@extends('layouts.app') {{-- Asume que tienes un layout principal, como layouts/app.blade.php --}}

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold mb-6 text-bluey-dark">¿Tienes dudas? Aquí están las respuestas.</h1>
        <p class=" text-xl text-bluey-dark mb-5">Hemos reunido las preguntas más frecuentes sobre el bienestar animal y sus respuestas prácticas.</p>

        @if($faqs->isEmpty())
            <p>No hay preguntas frecuentes disponibles en este momento.</p>
        @else
            <div class="space-y-6">
                @foreach($faqs as $faq)
                    <div class="border rounded-lg shadow-sm p-6 bg-white">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-2">
                            {{ $faq->title }}
                        </h2>
                        {{-- Muestra el contenido HTML/JSON del Tiptap Editor --}}
                        <div class="prose max-w-none"> {{-- La clase 'prose' de Tailwind Typography es ideal aquí --}}
                            {!! $faq->content !!} {{-- ¡Importante! Usa {!! !!} para renderizar HTML sin escapar --}}
                        </div>

                        @if($faq->excerpt)
                            <p class="text-gray-600 mt-4">
                                **Resumen:** {{ $faq->excerpt }}
                            </p>
                        @endif

                        {{-- Enlace a la página individual de la FAQ (si implementaste show) --}}
                        {{-- @if (Route::has('faqs.show'))
                            <a href="{{ route('faqs.show', $faq->slug) }}" class="text-blue-600 hover:underline mt-4 inline-block">
                                Ver más detalles
                            </a>
                        @endif --}}
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection