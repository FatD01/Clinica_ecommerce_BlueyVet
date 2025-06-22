<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BlueyVet - @yield('title', 'Panel de Control')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    {{-- Importa CSS y JS de Vite. app-vet.css contendrá Bootstrap y FullCalendar CSS.
         app-vet.js contendrá Bootstrap y FullCalendar JS. --}}
    @vite(['resources/css/app-vet.css', 'resources/js/app-vet.js'])

    {{-- Aquí es donde se inyectan los estilos específicos de cada vista, si es necesario.
         (En este caso, para 'citas-agendadas', ya no deberías necesitar cargar CSS aquí). --}}
    @yield('styles')

</head>
<body>
    <div class="container-fluid-custom"> {{-- Clase para tu layout principal --}}
        <aside class="sidebar">
            <div class="brand">
                <i class="fas fa-paw"></i> BlueyVet
            </div>
            <nav>
                <ul>
                    <li>
                        <a href="{{ route('veterinarian.citas') }}" class="{{ request()->routeIs('veterinarian.citas') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check"></i> Consultar Citas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('historialmedico.index') }}" class="{{ request()->routeIs('historialmedico.index') ? 'active' : '' }}">
                            <i class="fas fa-file-medical"></i> Historial Médico
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('veterinarian.profile') }}"
                            class="{{ request()->routeIs('veterinarian.profile') || request()->routeIs('veterinarian.edit', '*') ? 'active' : '' }}">
                            Mi Información
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('datosestadisticos') }}" class="{{ request()->routeIs('datosestadisticos') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i> Datos estadísticos
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="user">
                Hola, <strong>{{ Auth::user()->name ?? 'Usuario' }}</strong>
            </div>
        </aside>

        <main class="main">
            <div class="header">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-logout" type="submit">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                    </button>
                </form>
            </div>

            <div class="content">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Aquí se inyectan los scripts específicos de cada vista (como la inicialización de FullCalendar).
         ES MUY IMPORTANTE que esto esté DESPUÉS de la carga de app-vet.js. --}}
    @yield('scripts')

</body>
</html>