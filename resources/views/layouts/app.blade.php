<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Añadir CSRF token para seguridad --}}

    <title>Vet Bluey - @yield('title', 'Inicio')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- ******************************************************************************** --}}
    {{-- AHORA ES CRUCIAL QUE TU TAILWIND Y TU CSS PERSONALIZADO SEAN PARTE DE app.css --}}
    {{-- Si 'resources/css/general.css' tiene tu CSS personalizado, asegúrate de que esté --}}
    {{-- importado dentro de 'resources/css/app.css'. --}}
    {{-- Por ejemplo, en 'resources/css/app.css' deberías tener: --}}
    {{-- @tailwind base; --}}
    {{-- @tailwind components; --}}
    {{-- @tailwind utilities; --}}
    {{-- @import './general.css'; /* Si general.css existe y contiene tu estilo principal */ --}}
    {{-- ******************************************************************************** --}}

    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Esto carga el CSS/JS principal de Vite --}}

    @stack('css') {{-- Para CSS específico de cada vista que use @push('css') --}}
</head>
<body>
    @include('client.includes.navbar') {{-- Tu navbar global --}}

    <main class="main">
        <div class="container"> {{-- Asegúrate de que este 'container' sea el que uses para centrar contenido --}}
            @yield('content')
        </div>
    </main>

    <footer>
        @includeWhen(View::exists('client.includes.footer'), 'client.includes.footer')
    </footer>

    {{-- Scripts adicionales. @vite ya carga app.js, que puede incluir Alpine.js y otros. --}}
    @stack('js') {{-- Para JS específico de cada vista que use @push('js') --}}
</body>
</html>