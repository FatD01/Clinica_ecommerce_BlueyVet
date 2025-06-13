import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function() {

    // --- Referencias a elementos (mejor obtenerlas una vez) ---
    const userProfileMenuButton = document.getElementById('user-profile-menu-button');
    const userProfileDropdownMenu = document.getElementById('user-profile-dropdown-menu');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileProductDropdownToggle = document.getElementById('mobile-product-dropdown-toggle');
    const mobileProductDropdownMenu = document.getElementById('mobile-product-dropdown-menu');
    const carritoWindow = document.getElementById('carritoWindow');
    const carritoIcono = document.getElementById('carrito-icono');
    const closeCartBtn = carritoWindow ? carritoWindow.querySelector('.head-carrito .close-btn-carrito') : null;
    const navbar = document.querySelector('.navbar');
    const appointmentDetailModal = document.getElementById('appointmentDetailModal');


    // Helper function para cerrar un dropdown (si está abierto)
    // Asegura que las clases de ocultamiento estén presentes y de rotación no
    function hideDropdown(dropdownElement, toggleButton = null) {
        if (!dropdownElement) return;

        // Para dropdowns controlados por opacity/visibility/scale
        if (dropdownElement.classList.contains('visible')) { // Si está visible por esta convención
            dropdownElement.classList.add('opacity-0', 'invisible', 'scale-95');
            dropdownElement.classList.remove('visible');
        }
        // Para dropdowns controlados por 'hidden'
        if (!dropdownElement.classList.contains('hidden')) { // Si NO tiene 'hidden' (está visible)
            dropdownElement.classList.add('hidden');
        }

        // Rotar el icono si existe
        if (toggleButton && toggleButton.querySelector('.bi-chevron-down')) {
            toggleButton.querySelector('.bi-chevron-down').classList.remove('rotate-180');
        }
    }

    // Helper function para mostrar un dropdown (si está oculto)
    // Asegura que las clases de ocultamiento no estén presentes y de rotación sí
    function showDropdown(dropdownElement, toggleButton = null) {
        if (!dropdownElement) return;

        // Para dropdowns controlados por opacity/visibility/scale
        if (!dropdownElement.classList.contains('visible')) { // Si no está visible por esta convención
            dropdownElement.classList.remove('opacity-0', 'invisible', 'scale-95');
            dropdownElement.classList.add('visible');
        }
        // Para dropdowns controlados por 'hidden'
        if (dropdownElement.classList.contains('hidden')) { // Si tiene 'hidden' (está oculto)
            dropdownElement.classList.remove('hidden');
        }

        // Rotar el icono si existe
        if (toggleButton && toggleButton.querySelector('.bi-chevron-down')) {
            toggleButton.querySelector('.bi-chevron-down').classList.add('rotate-180');
        }
    }

    // --- Función para cerrar TODOS los menús (excepto el que se va a abrir, si se especifica) ---
    function closeAllActiveMenus(exceptElementId = null) {
        // Cierra menú de perfil si no es el excluido
        if (userProfileDropdownMenu && userProfileMenuButton && userProfileDropdownMenu.id !== exceptElementId) {
            hideDropdown(userProfileDropdownMenu, userProfileMenuButton);
        }

        // Cierra menú móvil y su sub-menú de productos si no es el excluido
        if (mobileMenu && mobileMenuButton && mobileMenu.id !== exceptElementId) {
            hideDropdown(mobileMenu, mobileMenuButton);
            hideDropdown(mobileProductDropdownMenu, mobileProductDropdownToggle); // Asegurar que el submenú también se cierre
        }

        // Cierra carrito si no es el excluido
        if (carritoWindow && carritoIcono && carritoWindow.id !== exceptElementId) {
            hideDropdown(carritoWindow);
        }
    }


    // 1. Menú de Usuario/Perfil
    if (userProfileMenuButton && userProfileDropdownMenu) {
        userProfileMenuButton.addEventListener('click', function(event) {
            event.stopPropagation(); // IMPORTANTE: Solo stopPropagation en el click del botón para evitar que el click global lo cierre inmediatamente

            // Si está visible, ocultarlo. Si no, ocultar otros y mostrar este.
            if (userProfileDropdownMenu.classList.contains('visible')) {
                hideDropdown(userProfileDropdownMenu, userProfileMenuButton);
            } else {
                closeAllActiveMenus(userProfileDropdownMenu.id); // Cerrar todos excepto este
                showDropdown(userProfileDropdownMenu, userProfileMenuButton);
            }
        });
    }

    // 2. Menú Móvil (Hamburguesa)
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function(event) {
            event.stopPropagation(); // Para que el click en el botón de hamburguesa no cierre algo más

            if (!mobileMenu.classList.contains('hidden')) { // Si está visible
                hideDropdown(mobileMenu, mobileMenuButton);
                hideDropdown(mobileProductDropdownMenu, mobileProductDropdownToggle); // También el submenú si está abierto
            } else {
                closeAllActiveMenus(mobileMenu.id); // Cerrar otros menús antes de abrir este
                showDropdown(mobileMenu, mobileMenuButton);
            }
        });
    }

    // 3. Dropdown de Productos (Desktop) - Se sigue manejando con CSS hover. No hay JS aquí.


    // 4. Dropdown de Productos (Mobile, dentro del menú hamburguesa)
    if (mobileProductDropdownToggle && mobileProductDropdownMenu) {
        mobileProductDropdownToggle.addEventListener('click', function(event) {
            event.preventDefault(); // Prevenir navegación si el href es '#'
            event.stopPropagation(); // Evita que el clic en el toggle del submenú cierre el menú móvil principal

            if (mobileProductDropdownMenu.classList.contains('hidden')) { // Si está oculto
                showDropdown(mobileProductDropdownMenu, mobileProductDropdownToggle);
            } else {
                hideDropdown(mobileProductDropdownMenu, mobileProductDropdownToggle);
            }
        });
    }


    // 5. Carrito
    if (carritoIcono && carritoWindow) {
        carritoIcono.addEventListener('click', function(event) {
            event.stopPropagation(); // Para que el click en el carrito no cierre otros menús por el listener global

            if (carritoWindow.classList.contains('visible')) { // Si está visible
                hideDropdown(carritoWindow);
            } else {
                closeAllActiveMenus(carritoWindow.id); // Cerrar otros menús antes de abrir este
                showDropdown(carritoWindow);
            }
        });

        if (closeCartBtn) {
            closeCartBtn.addEventListener('click', (event) => {
                event.stopPropagation(); // Evitar que el clic en la X se propague
                hideDropdown(carritoWindow);
            });
        }
    }


    // 6. Cierre global al hacer clic fuera de cualquier menú/dropdown
    document.addEventListener('click', function(event) {
        // Cierre del menú de perfil si el clic NO fue dentro de él o su botón
        if (userProfileDropdownMenu && userProfileMenuButton &&
            !userProfileDropdownMenu.contains(event.target) &&
            !userProfileMenuButton.contains(event.target)) {
            hideDropdown(userProfileDropdownMenu, userProfileMenuButton);
        }

        // Cierre del menú móvil y su submenú si el clic NO fue dentro de él o su botón
        if (mobileMenu && mobileMenuButton &&
            !mobileMenu.contains(event.target) &&
            !mobileMenuButton.contains(event.target)) {
            hideDropdown(mobileMenu, mobileMenuButton);
            hideDropdown(mobileProductDropdownMenu, mobileProductDropdownToggle);
        }

        // Cierre del carrito si el clic NO fue dentro de él o su icono
        if (carritoWindow && carritoIcono &&
            !carritoWindow.contains(event.target) &&
            !carritoIcono.contains(event.target)) {
            hideDropdown(carritoWindow);
        }
    });

    // 7. Cierre global al presionar ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            hideDropdown(userProfileDropdownMenu, userProfileMenuButton);
            hideDropdown(mobileMenu, mobileMenuButton);
            hideDropdown(mobileProductDropdownMenu, mobileProductDropdownToggle);
            hideDropdown(carritoWindow);
        }
    });

    // 8. Efecto de scroll para el navbar
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 9. Lógica para el modal de detalles de citas
    if (appointmentDetailModal) {
        appointmentDetailModal.addEventListener('show.bs.modal', function (event) {
            let button = event.relatedTarget;
            let modalTitle = appointmentDetailModal.querySelector('.modal-title');
            let modalBody = appointmentDetailModal.querySelector('.modal-body');

            modalTitle.textContent = 'Detalles de la Cita #' + (button.dataset.id ?? '');
            modalBody.innerHTML = `
                <p><strong>Mascota:</strong> ${button.dataset.mascotaName ?? 'N/A'}</p>
                <p><strong>Fecha y Hora:</strong> ${button.dataset.date ?? 'N/A'}</p>
                <p><strong>Servicio:</strong> ${button.dataset.serviceName ?? 'N/A'}</p>
                <p><strong>Veterinario:</strong> ${button.dataset.veterinarianName ?? 'N/A'}</p>
                <p><strong>Motivo:</strong> ${button.dataset.reason ?? 'N/A'}</p>
                <p><strong>Estado:</strong> ${button.dataset.status ?? 'N/A'}</p>
                <p><strong>Costo:</strong> ${button.dataset.amount ? parseFloat(button.dataset.amount).toFixed(2) : 'N/A'} ${button.dataset.currency ?? ''}</p>
            `;
        });
    }
});