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

    {{-- CDN de Bootstrap Icons y Font Awesome --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- ESTO ES LO ÚNICO QUE NECESITAS PARA TUS ESTILOS Y SCRIPTS DE LA APLICACIÓN --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('css') {{-- Mantén esto para cualquier CSS específico de una vista individual --}}
    {{-- Si citas.css está importado en app.css, la siguiente línea NO es necesaria. --}}
</head>

<body> {{-- Aquí inicia la única etiqueta body --}}

    {{-- Navbar --}}
    @include('client.includes.navbar')

    {{-- Contenido principal de la página --}}
    <main class="main">
        <div class="container">
            @yield('content') {{-- Aquí se inyecta el contenido de tus vistas (ej. petshop.blade.php) --}}
        </div>
    </main>

    {{-- Footer --}}
    <footer>
        @includeWhen(View::exists('client.includes.footer'), 'client.includes.footer')
    </footer>

    <div id="productOrderConfirmationModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); justify-content: center; align-items: center;">
        <div class="modal-content bg-white p-8 rounded-lg shadow-xl relative text-center w-11/12 max-w-lg">
            <span class="close-button absolute top-4 right-6 text-gray-500 text-3xl font-bold cursor-pointer hover:text-gray-700">&times;</span>
            <h2 class="text-bluey-dark text-3xl font-bold mb-4">¡Compra Confirmada!</h2>
            <p class="text-gray-700 text-lg mb-3">Gracias por tu compra. Tu pedido #<span id="modalOrderId" class="font-bold"></span> ha sido procesado con éxito.</p>
            <p class="text-gray-600 text-base mb-6">Se ha enviado un correo de confirmación con los detalles de tu pedido y el comprobante adjunto a tu email registrado.</p>
            <div class="flex flex-col gap-3">
                <a id="downloadInvoiceLink" href="#" target="_blank" class="px-6 py-3 rounded-md text-white font-semibold bg-bluey-primary hover:bg-bluey-dark transition-colors duration-300">Descargar Comprobante PDF</a>
                <a id="continueShoppingButton" href="{{ route('client.products.petshop') }}"
                    class="px-6 py-3 rounded-md text-white font-semibold bg-bluey-secondary hover:bg-bluey-secondary-light transition-colors duration-300">
                    Seguir Comprando
                </a>
            </div>
        </div>
    </div>


    {{-- Scripts adicionales. @vite ya carga app.js, que puede incluir Alpine.js y otros. --}}
    {{-- Mantenemos @stack('scripts') para que puedas agregar scripts específicos de una vista si lo necesitas --}}
    @stack('scripts')

    {{-- Script para el modal de confirmación de pedido (debe ir después del HTML del modal) --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const showModal = {{ session()->has('show_product_order_modal') ? 'true' : 'false' }};
        const orderId = {{ session()->get('confirmed_product_order_id', 'null') }};
        
        const modal = document.getElementById('productOrderConfirmationModal');
        if (modal) {
            const closeButton = modal.querySelector('.close-button');
            const modalOrderIdSpan = document.getElementById('modalOrderId');
            const downloadInvoiceLink = document.getElementById('downloadInvoiceLink');
            const continueShoppingButton = document.getElementById('continueShoppingButton'); // Selecciona el nuevo botón

            if (showModal && orderId !== null) {
                modalOrderIdSpan.textContent = orderId;
                downloadInvoiceLink.href = `{{ route('download.product_invoice', ['order' => ':orderId']) }}`.replace(':orderId', orderId);
                
                modal.style.display = 'flex'; // Usar flexbox para centrar
                document.body.style.overflow = 'hidden'; // Evita el scroll del fondo

                // Cierra el modal al hacer clic en la 'x'
                closeButton.onclick = function() {
                    modal.style.display = 'none';
                    document.body.style.overflow = ''; // Restaurar scroll
                }

                // Cierra el modal al hacer clic fuera de él
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                        document.body.style.overflow = ''; // Restaurar scroll
                    }
                }

                // AÑADE ESTO: Cierra el modal al hacer clic en "Seguir Comprando"
                if (continueShoppingButton) {
                    continueShoppingButton.addEventListener('click', function() {
                        modal.style.display = 'none';
                        document.body.style.overflow = ''; // Restaurar scroll
                        // La redirección ocurrirá naturalmente por el 'href' del enlace
                    });
                }
            }
        }
    });
</script>

    {{-- Script de la barra de navegación (esto es específico de tu navbar) --}}
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

</body> {{-- Aquí finaliza la única etiqueta body --}}

</html>