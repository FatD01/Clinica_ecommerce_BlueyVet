{{-- resources/views/client/pages/about.blade.php --}}

@extends('layouts.app')

@section('title', 'Sobre Nosotros - BlueyVet')

@section('content')
<div class="container mx-auto p-6 md:p-12 select-none">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden p-8 md:p-12">
        {{-- Título principal de la sección --}}
        <h1 class="text-4xl md:text-5xl font-bold text-center text-bluey-dark mb-8">
            🐾 Sobre Nosotros <span class="text-bluey-primary">BlueyVet</span>
        </h1>

        {{-- Contenido principal de la página --}}
        <div class="text-lg text-bluey-dark leading-relaxed space-y-6">
            <p>En BlueyVet, somos un equipo comprometido con la innovación tecnológica al servicio del cuidado animal. Nuestra misión es ofrecer una plataforma web moderna, intuitiva y completa que permita a las clínicas veterinarias optimizar su gestión, mejorar la experiencia de sus clientes y brindar una atención de calidad a las mascotas.</p>

            <p>Este proyecto nace de la pasión por la tecnología y el amor por los animales. Como estudiantes de Ingeniería de Software, identificamos una necesidad real en el sector veterinario: la falta de herramientas digitales que unifiquen atención médica y venta de productos. Por eso, diseñamos BlueyVet como una solución integral que combina la gestión clínica veterinaria con un sistema de e-commerce eficiente y accesible.</p>

            <h2 class="text-3xl font-semibold text-bluey-dark mt-8 mb-4">Nuestro sistema permite a los usuarios:</h2>
            <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Registrar y gestionar sus mascotas.</li>
                <li>Agendar y seguir citas médicas.</li>
                <li>Consultar historiales médicos.</li>
                <li>Comprar productos veterinarios desde casa.</li>
                <li>Recibir recordatorios importantes y atención personalizada.</li>
            </ul>

            <h2 class="text-3xl font-semibold text-bluey-dark mt-8 mb-4">Y a las clínicas:</h2>
            <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Gestionar inventario, personal y citas.</li>
                <li>Automatizar tareas administrativas.</li>
                <li>Ofrecer servicios más rápidos y organizados.</li>
            </ul>

            <h2 class="text-3xl font-semibold text-bluey-dark mt-8 mb-4">👩‍💻 ¿Quiénes estamos detrás?</h2>
            <p>Somos tres desarrolladores entusiastas, dedicados a construir soluciones digitales con impacto real:</p>
            <ul class="list-disc list-inside ml-4 space-y-2 font-semibold">
                {{-- Primer Desarrollador --}}
                <li class="flex items-center">
                    {{-- Enlace en el nombre --}}
                    <a href="https://github.com/FatD01" target="_blank" rel="noopener noreferrer"
                       class="text-bluey-dark hover:text-bluey-primary underline mr-2">
                        Fatima Celeste Rodriguez Castrejón
                    </a>
                    {{-- Enlace en el icono de GitHub --}}
                    <a href="https://github.com/FatD01" target="_blank" rel="noopener noreferrer"
                       class="text-bluey-dark hover:text-bluey-primary">
                        <i class="fa-brands fa-github text-xl"></i>
                    </a>
                </li>

                {{-- Segundo Desarrollador --}}
                <li class="flex items-center">
                    <a href="https://github.com/SkimerPM" target="_blank" rel="noopener noreferrer"
                       class="text-bluey-dark hover:text-bluey-primary underline mr-2">
                        José Fabricio Sanchez Quiroz
                    </a>
                    <a href="https://github.com/SkimerPM" target="_blank" rel="noopener noreferrer"
                       class="text-bluey-dark hover:text-bluey-primary">
                        <i class="fa-brands fa-github text-xl"></i>
                    </a>
                </li>

                {{-- Tercer Desarrollador --}}
                <li class="flex items-center">
                    {{-- ¡¡¡AQUÍ DEBES REEMPLAZAR LA URL!!! --}}
                    <a href="https://github.com/ANTONYVARASRODRIGUEZ" target="_blank" rel="noopener noreferrer"
                       class="text-bluey-dark hover:text-bluey-primary underline mr-2">
                        Antony Raid Varas Rodriguez
                    </a>
                    {{-- ¡¡¡AQUÍ DEBES REEMPLAZAR LA URL!!! --}}
                    <a href="https://github.com/ANTONYVARASRODRIGUEZ" target="_blank" rel="noopener noreferrer"
                       class="text-bluey-dark hover:text-bluey-primary">
                        <i class="fa-brands fa-github text-xl"></i>
                    </a>
                </li>
            </ul>

            <p>Juntos hemos diseñado BlueyVet como un proyecto que combina funcionalidad, usabilidad y pasión por el bienestar animal. Nuestro objetivo es que más clínicas puedan decirle adiós al papel y dar el salto hacia la digitalización con confianza.</p>

            <p class="text-center text-xl font-bold text-bluey-primary mt-10">
                Gracias por confiar en BlueyVet. ¡Tu mascota está en buenas patas! 🐶🐱
            </p>
        </div>
    </div>
</div>
@endsection