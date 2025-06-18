{{-- resources/views/faqs/show.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <a href="{{ route('faqs.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Volver a Preguntas Frecuentes</a>

        <h1 class="text-3xl font-bold mb-4">{{ $faq->title }}</h1>
        <p class="text-gray-600 text-sm mb-6">Publicado el: {{ $faq->published_at->format('d/m/Y') }}</p>

        <div class="prose max-w-none">
            {!! $faq->content !!}
        </div>
    </div>
@endsection