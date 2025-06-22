{{-- resources/views/client/pages/contact.blade.php --}}

{{-- Extiende tu layout principal --}}
@extends('layouts.app')

{{-- Título de la página --}}
@section('title', 'Contáctanos - BlueyVet')

{{-- Contenido principal --}}
@section('content')
{{-- Hero Section de Contacto (similar a otros heroes) --}}
<section class="relative py-20 px-6 overflow-hidden bg-bluey-dark">
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('img/bg-hero-contact.jpg') }}" alt="Consultorio veterinario moderno" class="w-full h-full object-cover object-center">
        <div class="absolute inset-0 bg-bluey-dark opacity-70"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-bluey-primary/30 to-bluey-secondary/30"></div>
    </div>

    <div class="container mx-auto text-center relative z-10">
        <h1 class="text-4xl lg:text-5xl font-bold mb-4 text-white leading-tight">
            Estamos Aquí para <span class="text-bluey-light-yellow">Ayudarte</span>
        </h1>
        <p class="text-xl text-white max-w-2xl mx-auto">
            Tu bienestar y el de tu mascota son nuestra prioridad. Contáctanos para cualquier consulta o emergencia.
        </p>
    </div>
</section>

{{-- Sección de Información de Contacto --}}
<section class="py-16 px-6 bg-gray-100">
    <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-12">
        {{-- Detalles de Contacto --}}
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold text-bluey-dark mb-6">Nuestra Información</h2>
            <div class="space-y-6 text-lg text-gray-700">
                <p class="flex items-center">
                    <i class="fas fa-map-marker-alt text-bluey-primary mr-3 text-2xl"></i>
                    <span class="font-semibold">Dirección:</span> Av. España 123, Trujillo, La Libertad, Perú
                </p>
                <p class="flex items-center">
                    <i class="fas fa-phone-alt text-bluey-primary mr-3 text-2xl"></i>
                    <span class="font-semibold">Teléfono:</span> <a href="tel:+51944280482" class="hover:underline text-bluey-dark">+51 944 280 482</a>
                </p>
                <p class="flex items-center">
                    <i class="fas fa-envelope text-bluey-primary mr-3 text-2xl"></i>
                    <span class="font-semibold">Email:</span> <a href="mailto:bueyvet@gmail.com" class="hover:underline text-bluey-dark"> bueyvet@gmail.com
                    </a>
                </p>
                <p class="flex items-center">
                    <i class="fas fa-clock text-bluey-primary mr-3 text-2xl"></i>
                    <span class="font-semibold">Horario:</span> 24Hrs, Lunes a Domingo
                </p>
            </div>
        </div>

        {{-- Formulario de Contacto (Placeholder) --}}
        {{-- Formulario de Contacto --}}
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold text-bluey-dark mb-6">Envíanos un Mensaje</h2>

            {{-- Mensajes de éxito o error --}}
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            {{-- Mensajes de validación de Laravel --}}
            @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <strong class="font-bold">¡Ups!</strong>
                <span class="block sm:inline">Hay algunos problemas con tu envío:</span>
                <ul class="mt-3 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                @csrf {{-- Protección CSRF --}}

                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Tu Nombre</label>
                    <input type="text" id="name" name="name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-bluey-primary @error('name') border-red-500 @enderror"
                        value="{{ old('name', Auth::check() ? Auth::user()->name : '') }}" required>
                    @error('name')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Tu Email</label>
                    <input type="email" id="email" name="email"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-bluey-primary @error('email') border-red-500 @enderror"
                        value="{{ old('email', Auth::check() ? Auth::user()->email : '') }}" required>
                    @error('email')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subject" class="block text-gray-700 text-sm font-bold mb-2">Asunto (Opcional)</label>
                    <input type="text" id="subject" name="subject"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-bluey-primary @error('subject') border-red-500 @enderror"
                        value="{{ old('subject') }}">
                    @error('subject')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Tu Mensaje</label>
                    <textarea id="message" name="message" rows="5"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-bluey-primary @error('message') border-red-500 @enderror" required>{{ old('message') }}</textarea>
                    @error('message')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="bg-bluey-primary hover:bg-bluey-dark text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                    Enviar Mensaje
                </button>
            </form>
        </div>
    </div>
</section>

{{-- Sección de Mapa (Opcional) --}}
<section class="py-16 px-6 bg-white">
    <div class="container mx-auto text-center">
        <h2 class="text-3xl font-bold text-bluey-dark mb-6">Encuéntranos en el Mapa</h2>
        <div class="relative w-full overflow-hidden rounded-lg shadow-lg" style="padding-top: 56.25%;"> {{-- 16:9 Aspect Ratio --}}
            {{-- Reemplaza este iframe con el código de un mapa embebido de Google Maps --}}
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1012826.0016386887!2d-80.14348701834616!3d-7.426289757353609!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91ad3d47e1fef7f9%3A0xc69536bb4839b47b!2sInstituto%20Superior%20Tecsup%20El%20Golf!5e0!3m2!1ses!2spe!4v1750284089068!5m2!1ses!2spe" {{-- ¡Mantén tu SRC aquí! --}}
                style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                class="absolute top-0 left-0 w-full h-full"></iframe> {{-- Estas clases son clave --}}
            {{-- Para obtener tu propio mapa: ve a Google Maps, busca tu ubicación, haz clic en "Compartir", luego en "Insertar un mapa" y copia el código del iframe. --}}
        </div>
    </div>
</section>
@endsection