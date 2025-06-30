<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('img/logo-blueyvet.png') }}" type="image/png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/logo-blueyvet.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('tu_logo_nuevo_32x32.png') }}">

    <title>Vet Bluey - @yield('title', 'Inicio')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- CDN de Bootstrap Icons y Font Awesome --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Flatpickr CSS GLOBAL (Colocado antes de @vite para una mejor cascada CSS) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    {{-- Opcional: Tema de Flatpickr (ej. "material_blue"). Puedes elegir otro o ninguno. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

    {{-- ESTO ES LO ÚNICO QUE NECESITAS PARA TUS ESTILOS Y SCRIPTS DE LA APLICACIÓN --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('css') {{-- Mantén esto para cualquier CSS específico de una vista individual --}}
</head>

<body> {{-- Aquí inicia la única etiqueta body --}}

    {{-- Navbar --}}
    @include('client.includes.navbar')

    {{-- Contenido principal de la página --}}
    <main class="main">
        <div class="container">
           

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">¡Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display='none';">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Cerrar</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            @endif

            @if (session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Información:</strong>
                    <span class="block sm:inline">{{ session('info') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display='none';">
                        <svg class="fill-current h-6 w-6 text-blue-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Cerrar</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            @endif
            {{-- FIN BLOQUE DE ALERTAS FLASH --}}

            @yield('content') {{-- Aquí se inyecta el contenido de tus vistas (ej. petshop.blade.php) --}}
        </div>
    </main>

    {{-- Footer --}}
    <footer>
        @includeWhen(View::exists('client.includes.footer'), 'client.includes.footer')
    </footer>

    {{-- Modal de Confirmación de Orden de Producto --}}
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

    {{-- Modal de Confirmación de Cita --}}
    <div id="appointmentConfirmationModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); justify-content: center; align-items: center;">
        <div class="modal-content bg-white p-8 rounded-lg shadow-xl relative text-center w-11/12 max-w-lg">
            <span class="close-button absolute top-4 right-6 text-gray-500 text-3xl font-bold cursor-pointer hover:text-gray-700">&times;</span>
            <h2 class="text-bluey-dark text-3xl font-bold mb-4">¡Cita Agendada Exitosamente!</h2>
            <p class="text-gray-700 text-lg mb-3">
                ¡Gracias por agendar tu cita con nosotros! Tu cita para
                <span id="modalAppointmentService" class="font-bold"></span> con el Dr./Dra.
                <span id="modalAppointmentVeterinarian" class="font-bold"></span> para tu mascota
                <span id="modalAppointmentMascota" class="font-bold"></span> ha sido agendada para el
                <span id="modalAppointmentDateTime" class="font-bold"></span>.
            </p>
            <p class="text-gray-600 text-base mb-6">
                Se ha enviado un correo a tu dirección registrada (<span id="modalAppointmentEmail" class="font-bold"></span>)
                con el comprobante de la cita y los detalles de tu pago (<span id="modalAppointmentPaymentMethod" class="font-bold"></span>).
                Por favor, revisa tu bandeja de entrada y spam.
            </p>
            <div class="flex flex-col gap-3">
                {{-- Botón para descargar el comprobante de la cita (EXISTENTE) --}}
                <a id="downloadAppointmentInvoiceLink" href="#" target="_blank" class="px-6 py-3 rounded-md text-white font-semibold bg-bluey-primary hover:bg-bluey-dark transition-colors duration-300">
                    <i class="fas fa-file-invoice"></i> Descargar Comprobante Cita PDF
                </a>
                {{-- NUEVO: Botón para descargar el recibo de pago --}}
                <a id="downloadPaymentReceiptLink" href="#" target="_blank" class="px-6 py-3 rounded-md text-white font-semibold bg-bluey-secondary hover:bg-bluey-secondary-light transition-colors duration-300" style="display: none;">
                    <i class="fas fa-receipt"></i> Descargar Recibo de Pago PDF
                </a>
                {{-- Botón para ver todas las citas (EXISTENTE) --}}
                <a id="viewMyAppointmentsButton" href="{{ route('client.citas.index') }}"
                    class="px-6 py-3 rounded-md text-white font-semibold bg-gray-600 hover:bg-gray-700 transition-colors duration-300">
                    Ver Mis Citas
                </a>
                {{-- Botón para cerrar el modal desde el footer (EXISTENTE) --}}
                <button type="button" class="px-6 py-3 rounded-md text-bluey-dark font-semibold bg-gray-200 hover:bg-gray-300 transition-colors duration-300 close-button-appointment">
                    Cerrar
                </button>
            </div>
        </div>
    </div>


    {{-- Axios JS GLOBAL (Colocado antes de los scripts específicos de la app para que esté disponible) --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    {{-- Flatpickr JS GLOBAL (Colocado antes de los scripts específicos de la app para que esté disponible) --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    {{-- Opcional: Localización a español para Flatpickr --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    {{-- Script para los modales de confirmación (productos y citas) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Lógica para el modal de confirmación de orden de producto ---
            // Ensure these are directly outputting 'true', 'false', or the ID/null string
            const showProductModal = {{ session()->has('show_product_order_modal') ? 'true' : 'false' }};
            const productOrderId = {{ session()->get('confirmed_product_order_id', 'null') }};

            const productModal = document.getElementById('productOrderConfirmationModal');
            if (productModal) {
                const productCloseButton = productModal.querySelector('.close-button');
                const modalOrderIdSpan = document.getElementById('modalOrderId');
                const downloadInvoiceLink = document.getElementById('downloadInvoiceLink');
                const continueShoppingButton = document.getElementById('continueShoppingButton');

                // Check if the modal should be shown AND if productOrderId is not 'null'
                if (showProductModal && productOrderId !== 'null') {
                    modalOrderIdSpan.textContent = productOrderId;
                    // Only set href if productOrderId is a valid number
                    downloadInvoiceLink.href = `{{ route('download.product_invoice', ['order' => '__ORDER_ID__']) }}`.replace('__ORDER_ID__', productOrderId);

                    productModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';

                    productCloseButton.onclick = function() {
                        productModal.style.display = 'none';
                        document.body.style.overflow = '';
                    }

                    window.onclick = function(event) {
                        if (event.target == productModal) {
                            productModal.style.display = 'none';
                            document.body.style.overflow = '';
                        }
                    }

                    if (continueShoppingButton) {
                        continueShoppingButton.addEventListener('click', function() {
                            productModal.style.display = 'none';
                            document.body.style.overflow = '';
                        });
                    }
                }
            }


            // --- Lógica para el modal de confirmación de cita ---
            // Ensure these are directly outputting 'true', 'false', or the ID/null string
            const appointmentShowModal = {{ session()->has('show_appointment_confirmation_modal') ? 'true' : 'false' }};
            const appointmentData = {
                id: {{ session()->get('appointment_id', 'null') }},
                serviceOrderId: {{ session()->get('service_order_id_for_receipt', 'null') }},
                mascota: "{{ session()->get('mascota_name', '') }}",
                service: "{{ session()->get('service_name', '') }}",
                veterinarian: "{{ session()->get('veterinarian_name', '') }}",
                dateTime: "{{ session()->get('appointment_date_time', '') }}",
                paymentMethod: "{{ session()->get('payment_method', '') }}",
                userEmail: "{{ Auth::check() ? Auth::user()->email : '' }}"
            };

            const appointmentModal = document.getElementById('appointmentConfirmationModal');
            if (appointmentModal) {
                const appointmentCloseButton = appointmentModal.querySelector('.close-button');
                const appointmentCloseButtonFooter = appointmentModal.querySelector('.close-button-appointment');
                const modalAppointmentServiceSpan = document.getElementById('modalAppointmentService');
                const modalAppointmentVeterinarianSpan = document.getElementById('modalAppointmentVeterinarian');
                const modalAppointmentMascotaSpan = document.getElementById('modalAppointmentMascota');
                const modalAppointmentDateTimeSpan = document.getElementById('modalAppointmentDateTime');
                const modalAppointmentPaymentMethodSpan = document.getElementById('modalAppointmentPaymentMethod');
                const modalAppointmentEmailSpan = document.getElementById('modalAppointmentEmail');
                const downloadAppointmentInvoiceLink = document.getElementById('downloadAppointmentInvoiceLink');
                const downloadPaymentReceiptLink = document.getElementById('downloadPaymentReceiptLink');
                const viewMyAppointmentsButton = document.getElementById('viewMyAppointmentsButton');

                // Check if the modal should be shown AND if appointmentData.id is not 'null'
                if (appointmentShowModal && appointmentData.id !== 'null') {
                    modalAppointmentServiceSpan.textContent = appointmentData.service;
                    modalAppointmentVeterinarianSpan.textContent = appointmentData.veterinarian;
                    modalAppointmentMascotaSpan.textContent = appointmentData.mascota;
                    modalAppointmentDateTimeSpan.textContent = appointmentData.dateTime;
                    modalAppointmentPaymentMethodSpan.textContent = appointmentData.paymentMethod;
                    modalAppointmentEmailSpan.textContent = appointmentData.userEmail;

                    // Only set href if appointmentData.id is a valid number
                    downloadAppointmentInvoiceLink.href = `{{ route('download.appointment_invoice', ['appointmentId' => '__APPOINTMENT_ID__']) }}`.replace('__APPOINTMENT_ID__', appointmentData.id);


                    // Lógica para el botón del recibo de pago
                    // Verifica si serviceOrderId existe y no es la cadena 'null' antes de mostrar el botón
                    if (appointmentData.serviceOrderId && appointmentData.serviceOrderId !== 'null') {
                        downloadPaymentReceiptLink.href = `{{ route('download.payment_receipt', ['serviceOrderId' => '__SERVICE_ORDER_ID__']) }}`.replace('__SERVICE_ORDER_ID__', appointmentData.serviceOrderId);
                        downloadPaymentReceiptLink.style.display = ''; // Muestra el botón
                    } else {
                        downloadPaymentReceiptLink.style.display = 'none'; // Oculta el botón si no hay ID
                    }

                    appointmentModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';

                    const closeAppointmentModal = () => {
                        appointmentModal.style.display = 'none';
                        document.body.style.overflow = '';
                    };

                    appointmentCloseButton.onclick = closeAppointmentModal;
                    appointmentCloseButtonFooter.onclick = closeAppointmentModal;
                    window.addEventListener('click', function(event) {
                        if (event.target == appointmentModal) {
                            closeAppointmentModal();
                        }
                    });

                    if (viewMyAppointmentsButton) {
                        viewMyAppointmentsButton.addEventListener('click', closeAppointmentModal);
                    }
                }
            }

            // Script para el comportamiento de la barra de navegación (EXISTENTE y sin cambios)
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                });
            }
        });
    </script>

    {{-- Script duplicado de la barra de navegación: se mantiene pero el anterior es suficiente --}}
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


    @stack('scripts') {{-- Mantenemos esto para que puedas agregar scripts específicos de una vista si lo necesitas --}}

</body> {{-- Aquí finaliza la única etiqueta body --}}

</html>