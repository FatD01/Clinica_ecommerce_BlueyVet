<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueyVet - Cuidado profesional para tus mascotas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- Considera usar @vite para tu CSS de navbar.css si lo procesas con Tailwind --}}
    {{-- Ejemplo: @vite(['resources/css/client/navbar.css']) --}}
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
                        <a href="#" class="dropdown-item">
                            <i class="bi bi-capsule"></i> Farmacia
                        </a>
                        {{-- CAMBIADO A #: client.productos.petshop --}}
                        <a href="#" class="dropdown-item">
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

                <div id="carrito-icono" class="icon-btn cart-icon" title="Carrito">
                    <i class="bi bi-cart"></i>
                    <span class="cart-badge">0</span>
                </div>
<!-- 
            </div>
            <div id="carritoWindow" class="carrito-windows">
                <div class="head-carrito">
                    <h2>Productos en Carrito</h2>
                    <div>
                        <i class="bi bi-x"></i>
                    </div>
                </div>
                <div class="ps-carrito">
                    <div class="producto-c">
                        <div class="eliminar-de-carrito">
                            <i class="bi bi-trash-fill"></i>
                        </div>
                        <img src="https://picsum.photos/40/40" alt="Imagen aleatoria de prueba">
                        <p>Nombre Producto</p>
                        <Span class="precio-unidad">S/. 0.00</Span>
                        <div class="contadores">
                            <div class="contador-p"> <i class="bi bi-chevron-up"></i></div>
                            <div class="contador-p"> <i class="bi bi-chevron-down"></i></div>
                        </div>
                        <span class="cantidad">1</span>
                    </div>

                </div>
                <hr>
                <div class="calculos-carrito">
                    <p>SubTotal:</p>
                    <p class="valor-calculo">S/. 99.90</p>

                    <p>Descuento:</p>
                    <p class="valor-calculo">S/. 99.91</p>

                    <p>Envío:</p>
                    <p class="valor-calculo">S/. 99.92</p>

                    <p>Total:</p>
                    <p class="valor-calculo">S/. 99.93</p>
                </div>
                <div class="botones-carrito">
                    <button>Realizar pedido</button>
                    <button>Vaciar Carrito</button>
                </div> -->
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
<!-- <script>
    const carritoWindow = document.getElementById('carritoWindow');
    const carritoIcono = document.getElementById('carrito-icono');

    // Open cart window
    carritoIcono.addEventListener('click', function() {
        carritoWindow.classList.add('visible');
    });

    document.querySelector('.head-carrito i.bi-x').addEventListener('click', () => {
        carritoWindow.classList.remove('visible');
    });
</script> -->

</html>