<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Vet Bluey - @yield('title', 'Inicio')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- CDN de Bootstrap Icons y Font Awesome (están bien aquí si los necesitas globalmente) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- ESTO ES LO ÚNICO QUE NECESITAS PARA TUS ESTILOS Y SCRIPTS DE LA APLICACIÓN --}}
    @vite(['resources/css/app.css', 'resources/js/app.js']) 

    @stack('css') {{-- Mantén esto para cualquier CSS específico de una vista individual --}}
    {{-- ELIMINA ESTA LÍNEA si citas.css está importado en app.css --}}
    <!-- {{-- <link rel="stylesheet" href="{{ asset('css/citas.css') }}"> --}} -->
</head>
<body>
    @include('client.includes.navbar')

    <main class="main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer>
        @includeWhen(View::exists('client.includes.footer'), 'client.includes.footer')
    </footer>

    {{-- Scripts adicionales. @vite ya carga app.js, que puede incluir Alpine.js y otros. --}}
    @stack('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.querySelector('.navbar');

        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    });
    </script>
</body>
</html>