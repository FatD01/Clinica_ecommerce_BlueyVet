<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueyVet - Cuidado profesional para tus mascotas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- Considera usar @vite para tu CSS de navbar.css si lo procesas con Tailwind --}}
    {{-- Ejemplo: @vite(['resources/css/client/navbar.css']) --}}
    <link rel="stylesheet" href="{{ asset('css/Client/navbar.css')}}?v=1.1" />
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            {{-- CAMBIADO: Asumiendo que la ruta principal es la que quieres para el logo --}}
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('img/logo-blueyvet.png') }}" alt="BlueyVet" class="logo-img">
                <span class="logo-text">BLUEYVET</span>
            </a>
            <div class="menu">
                <div class="dropdown">
                    <button class="menu-link dropdown-toggle">
                        PRODUCTOS <i class="bi bi-chevron-down dropdown-icon"></i>
                    </button>
                    <div class="dropdown-menu">
                        {{-- CAMBIADO A #: client.productos.farmacia --}}
                        <!-- {{ route('productos.por_categoria', ['id' => 2]) }}  poner el id de la categoria farmacika-->
                        <!-- normalmente sería 2 primero ccreariamos petshop y clinica, en la bd,este no debe cambiar -->
                        <a href="{{ route('productos.por_categoria', ['id' => 3]) }}" class="dropdown-item">
                            <i class="bi bi-capsule"></i> Farmacia
                        </a>
                        <a href="{{ route('productos.por_categoria', ['id' => 1]) }}" class="dropdown-item">
                            <i class="bi bi-bag-heart"></i> Petshop
                        </a>
                    </div>
                </div>
                {{-- CAMBIADO A #: client.home. Si quieres que vaya a la ruta raíz, usa url('/') --}}
                <a href="{{ url('/') }}" class="menu-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house"></i> INICIO
                </a>
                {{-- CAMBIADO A #: client.blog.index --}}
                <a href="#" class="menu-link">
                    <i class="bi bi-newspaper"></i> BLOG
                </a>
                {{-- CAMBIADO A #: client.servicios.index --}}
                <a href="{{ route('client.servicios.index') }}" class="menu-link">
                    <i class="bi bi-heart-pulse"></i> SERVICIOS
                </a>
                {{-- CAMBIADO A #: client.citas.index --}}
                <a href="{{ route('client.citas.index') }}" class="menu-link">
                    <i class="bi bi-calendar-check"></i> CITAS
                </a>
            </div>
            <div class="action-icons">
                <a href="{{ route('login') }}" class="icon-btn user-icon" title="Mi cuenta">
                    <i class="bi bi-person"></i>
                </a>
                <!-- <div id="carrito-icono" class="icon-btn cart-icon" title="Carrito" onclick="toggleCart()">
                    <i class="bi bi-cart"></i>
                    <span id="cart-count" class="cart-badge">{{ count(session('cart', [])) }}</span>
                </div> -->

                <!-- <div id="carrito-icono" class="icon-btn cart-icon" title="Carrito">
                    <i class="bi bi-cart"></i>
                    <span id="cart-count" class="cart-badge">{{ count(session('cart', [])) }}</span>
                </div> -->

                <div id="carrito-icono" class="icon-btn cart-icon" title="Carrito" onclick="toggleCart()">
                    <i class="bi bi-cart"></i>
                    <span id="cart-count" class="cart-badge">{{ count(session('cart', [])) }}</span>
                </div>

                <x-cart-floating :cart="$cart" :total="$total" />
            </div>


            
        </div>
    </nav>
</body>
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

</html>