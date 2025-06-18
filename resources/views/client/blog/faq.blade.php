<x-guest-layout> {{-- O tu layout base para visitantes --}}
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <a href="{{ route('blog.index') }}" class="text-bluey-primary hover:underline mb-4 inline-block">&larr; Volver al Blog</a>

        <div class="bg-white rounded-lg shadow-lg p-8">
            @if($post->image_path)
                <img src="{{ asset('storage/' . $post->image_path) }}" alt="{{ $post->title }}" class="w-full h-80 object-cover rounded-lg mb-6">
            @else
                {{-- Asegúrate de tener esta imagen o ajusta la ruta --}}
                <img src="{{ asset('images/default_blog_hero.jpg') }}" alt="Imagen por defecto" class="w-full h-80 object-cover rounded-lg mb-6">
            @endif

            {{-- Usamos bluey-primary para un toque de color --}}
            <span class="text-bluey-primary text-sm font-semibold uppercase">{{ $post->category ?? 'General' }}</span>
            <h1 class="text-4xl font-bold text-bluey-dark mt-2 mb-4">{{ $post->title }}</h1>
            <p class="text-gray-500 text-sm mb-6">Publicado el {{ $post->published_at->format('d M, Y') }}</p>

            <div class="prose max-w-none text-gray-700 leading-relaxed">
                {!! $post->content !!} {{-- Usar !! para renderizar HTML (si tu contenido viene de un editor WYSIWYG) --}}
            </div>

            {{-- Aquí podrías añadir una sección de comentarios si decides implementarla --}}
        </div>
    </div>
</x-guest-layout>