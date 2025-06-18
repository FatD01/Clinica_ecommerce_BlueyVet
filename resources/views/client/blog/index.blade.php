@extends('layouts.app') {{-- Asume que tienes un layout principal, como layouts/app.blade.php --}}

@section('content')
    <div class="container mx-auto px-4 py-12 w-full">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h1 class="text-4xl font-bold text-bluey-dark mb-4">Nuestro blog veterinario está hecho para ti:</h1>
            <i class="fas fa-paw me-3 pb-1"></i>
            <p class="text-xl text-bluey-dark">Artículos, consejos y datos curiosos sobre el cuidado de tus mascotas.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" >
            @forelse($posts as $post)
                <div class="group bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border border-gray-100">
                    <div class="relative overflow-hidden h-56">
                        @if($post->image_path)
                            <img src="{{ asset('storage/' . $post->image_path) }}" alt="{{ $post->title }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <img src="{{ asset('images/default_blog_thumbnail.jpg') }}" alt="Imagen por defecto" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @endif
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                            <span class="inline-block px-3 py-1 bg-bluey-primary text-white text-xs font-semibold rounded-full">
                                {{ $post->category ?? 'General' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <span>{{ $post->published_at->format('d M, Y') }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-bluey-dark mb-3 leading-tight">{{ $post->title }}</h2>
                        <p class="text-gray-600 mb-4">{{ Str::limit($post->excerpt ?? strip_tags($post->content), 100) }}</p>
                        
                        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                            <a href="{{ route('blog.show', $post->slug) }}" 
                               class="text-bluey-primary hover:text-bluey-dark font-medium flex items-center transition-colors">
                                Leer más
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="bg-white p-8 rounded-xl shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-medium text-gray-600 mb-2">No hay artículos disponibles</h3>
                        <p class="text-gray-500">Pronto publicaremos nuevos contenidos.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-16">
            {{ $posts->links() }}
        </div>
    </div>
@endsection