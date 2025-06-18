<div
    id="cartFloating"
    class="select-none fixed top-0 right-0 w-[350px] h-screen p-4 shadow-[var(--medium-shadow)] z-[9999] transition-transform duration-300 bg-[var(--white)] border-l flex flex-col"
    style="border-color: var(--medium-gray); transform: translateX(100%);">

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-[var(--bluey-dark)]">Tu carrito</h3>
        <button onclick="toggleCart()" class="text-xl text-[var(--dark-gray)] hover:text-[var(--black)]">✖</button>
    </div>

    {{-- Contenedor para los ítems del carrito con scroll --}}
    <div class="flex-grow overflow-y-auto pr-2">
        @if (empty($cart)) {{-- $cart is expected from the controller --}}
        <p class="text-[var(--dark-gray)]" id="empty-cart-message">El carrito está vacío.</p>
        @else
        <ul class="space-y-4" id="cart-items-list">
            @foreach ($cart as $item) {{-- $cart is expected from the controller --}}
            @php
            $itemTotal = $item['effective_price_per_unit'] * $item['quantity'];
            @endphp
            <li class="border-b border-[var(--medium-gray)] pb-2 flex items-center" id="cart-item-{{ $item['id'] }}">
                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}"
                    class="w-16 h-16 object-cover rounded mr-3">

                <div class="flex-grow">
                    <strong class="text-[var(--black)] block">{{ $item['name'] }}</strong>

                    {{-- CAMBIO AQUÍ: Mostrar todos los títulos de las promociones si existen --}}
                    {{-- Usamos 'promotion_titles' (plural) que es un ARRAY --}}
                    <div class="promotion-info-container"> {{-- Nuevo contenedor para las promos --}}
                        @if (!empty($item['promotion_titles']))
                        @foreach ($item['promotion_titles'] as $promoTitle)
                        <p class="text-xs text-[var(--yellow-dark)] font-semibold mb-1">🎁 {{ $promoTitle }}</p>
                        @endforeach
                        @endif
                    </div>


                    {{-- Mostrar cantidad de regalo si aplica --}}
                    @if (isset($item['gift_quantity']) && $item['gift_quantity'] > 0)
                    <p class="text-xs text-green-600 font-semibold mb-1 gift-info">
                        ¡Llevas {{ $item['quantity'] }} + {{ $item['gift_quantity'] }} (gratis) = {{ $item['quantity'] + $item['gift_quantity'] }} productos!
                    </p>
                    @endif

                    {{-- Botones de cantidad --}}
                    <div class="flex items-center gap-2 my-2">
                        <button
                            class="px-2 py-1 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded update-cart text-sm"
                            data-id="{{ $item['id'] }}"
                            data-action="decrease"
                            {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>➖</button>

                        <span class="font-medium text-sm" id="qty-{{ $item['id'] }}">{{ $item['quantity'] }}</span>

                        <button
                            class="px-2 py-1 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded update-cart text-sm"
                            data-id="{{ $item['id'] }}"
                            data-action="increase"
                            {{ isset($item['stock']) && ($item['quantity'] + $item['gift_quantity']) >= $item['stock'] ? 'disabled' : '' }}>➕</button>
                    </div>

                    <small class="text-[var(--dark-gray)] block">
                        {{-- Muestra precio original tachado y precio con descuento si hay 'discounted_price' --}}
                        @if (isset($item['discounted_price']) && $item['discounted_price'] !== null && $item['discounted_price'] < $item['price'])
                            <span class="line-through text-red-500">${{ number_format($item['price'], 2) }}</span>
                            Precio: ${{ number_format($item['discounted_price'], 2) }} <br>
                            {{-- Si es 'buy_x_get_y', el precio a mostrar sigue siendo el original por unidad pagada --}}
                            @else
                            Precio: ${{ number_format($item['price'], 2) }} <br>
                            @endif
                            Subtotal: <span id="subtotal-{{ $item['id'] }}">${{ number_format($itemTotal, 2) }}</span>
                    </small>

                    <button
                        class="text-xs text-[var(--bluey-primary)] hover:text-[var(--bluey-dark)] remove-from-cart mt-1"
                        data-id="{{ $item['id'] }}">
                        Eliminar
                    </button>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    <hr class="my-4 border-[var(--medium-gray)]">

    <p class="text-[var(--black)] font-semibold text-right">
        Total: <span id="cart-total">${{ number_format($total, 2) }}</span> {{-- $total is expected from the controller --}}
    </p>

    {{-- AQUI ES DONDE CAMBIAS: ENVUELVE EL BOTON EN UN FORMULARIO --}}
    <form action="{{ route('cart_payments.pay') }}" method="POST">
        @csrf
        <button type="submit" class="w-full px-4 py-3 bg-[var(--bluey-primary)] hover:bg-[var(--bluey-secondary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200 mt-4">
            Realizar pedido
        </button>
    </form>
</div>
<script>
    let cartUpdateInProgress = false;

    function toggleCart() {
        const cart = document.getElementById('cartFloating');
        if (!cart) return;

        cart.style.transform = cart.style.transform === 'translateX(0%)' ?
            'translateX(100%)' :
            'translateX(0%)';
    }

    // Nueva función para manejar la eliminación de productos
    async function handleRemoveFromCart() {
        if (cartUpdateInProgress) return;
        cartUpdateInProgress = true;

        const productId = this.dataset.id;

        try {
            const res = await fetch(`/cart/remove/${productId}`, {
                method: 'DELETE', // Usar DELETE para eliminar
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await res.json();

            if (data.success) {
                // Eliminar el elemento del DOM
                const itemElement = document.getElementById(`cart-item-${productId}`);
                if (itemElement) {
                    itemElement.remove();
                }

                // Actualizar el total y el contador del carrito
                document.getElementById('cart-total').textContent = `$${data.total}`;
                document.getElementById('cart-count').textContent = data.cart_count;

                // Mostrar mensaje de carrito vacío si es necesario
                const cartItemsList = document.getElementById('cart-items-list');
                const emptyCartMessage = document.getElementById('empty-cart-message');
                if (data.cart_count === 0) {
                    cartItemsList.innerHTML = ''; // Limpiar la lista si no hay elementos
                    if (emptyCartMessage) {
                        emptyCartMessage.style.display = 'block'; // Mostrar el mensaje de carrito vacío
                    }
                } else {
                    if (emptyCartMessage) {
                        emptyCartMessage.style.display = 'none'; // Asegurarse de que esté oculto
                    }
                }

            } else {
                alert(data.message || 'Error al eliminar el producto.');
            }
        } catch (error) {
            console.error("Error eliminando producto del carrito:", error);
            alert('Hubo un error al procesar la eliminación del producto.');
        } finally {
            cartUpdateInProgress = false;
        }
    }

    async function handleUpdateCart() {
        if (cartUpdateInProgress) return;
        cartUpdateInProgress = true;

        const productId = this.dataset.id;
        const action = this.dataset.action;

        try {
            const res = await fetch(`/cart/update/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    action
                })
            });

            const data = await res.json();

            if (data.success) {
                // Actualizar elementos básicos
                document.getElementById(`qty-${productId}`).textContent = data.newQty;
                document.getElementById(`subtotal-${productId}`).textContent = `$${data.newSubtotal}`;
                document.getElementById('cart-total').textContent = `$${data.total}`;

                // Actualizar badge del carrito
                document.getElementById('cart-count').textContent = data.cart_count;

                // Actualizar botones de cantidad
                const increaseBtn = this.closest('.flex').querySelector('[data-action="increase"]');
                const decreaseBtn = this.closest('.flex').querySelector('[data-action="decrease"]');

                decreaseBtn.disabled = data.newQty <= 1;
                increaseBtn.disabled = data.newQty >= data.stock; // Asumiendo que 'data.stock' se envía desde el controlador

                // Obtener referencia al elemento del producto
                const itemElement = document.getElementById(`cart-item-${productId}`);

                // CAMBIO CLAVE AQUÍ: ACTUALIZAR BLOQUE DE PROMOCIÓN
                // Ahora esperamos un ARRAY 'promotion_titles'
                let promotionContainer = itemElement.querySelector('.promotion-info-container');
                if (!promotionContainer) {
                    // Si por alguna razón no existe, crearlo. Esto es para mayor robustez.
                    promotionContainer = document.createElement('div');
                    promotionContainer.className = 'promotion-info-container';
                    itemElement.querySelector('strong').after(promotionContainer);
                }
                promotionContainer.innerHTML = ''; // Limpiar las promociones anteriores

                if (data.promotion_titles && data.promotion_titles.length > 0) {
                    data.promotion_titles.forEach(title => {
                        const p = document.createElement('p');
                        p.className = 'text-xs text-[var(--yellow-dark)] font-semibold mb-1';
                        p.textContent = `🎁 ${title}`;
                        promotionContainer.appendChild(p);
                    });
                    promotionContainer.style.display = 'block';
                } else {
                    promotionContainer.style.display = 'none';
                }

                // ACTUALIZAR BLOQUE DE REGALO
                let giftElement = itemElement.querySelector('.gift-info');
                if (data.item_gift_quantity > 0) {
                    if (!giftElement) {
                        giftElement = document.createElement('p');
                        giftElement.className = 'text-xs text-green-600 font-semibold mb-1 gift-info';
                        // La referencia para insertar el elemento de regalo debe ser el contenedor de promociones
                        const reference = promotionContainer && promotionContainer.style.display !== 'none' ? promotionContainer : itemElement.querySelector('strong');
                        reference.after(giftElement);
                    }
                    giftElement.textContent =
                        `¡Llevas ${data.newQty} + ${data.item_gift_quantity} (gratis) = ${parseInt(data.newQty) + parseInt(data.item_gift_quantity)} productos!`;
                    giftElement.style.display = 'block';
                } else if (giftElement) {
                    giftElement.style.display = 'none';
                }

            } else {
                alert(data.message || 'Error al actualizar el carrito.');
            }
        } catch (error) {
            console.error("Error actualizando carrito:", error);
            alert('Hubo un error al procesar la actualización del carrito.');
        } finally {
            cartUpdateInProgress = false;
        }
    }
    // Esta función adjunta los escuchadores de eventos
    function attachCartListeners() {
        document.querySelectorAll('.update-cart').forEach(button => {
            // Es buena práctica remover el escuchador antes de añadirlo para evitar duplicados
            button.removeEventListener('click', handleUpdateCart);
            button.addEventListener('click', handleUpdateCart);
        });
        document.querySelectorAll('.remove-from-cart').forEach(button => {
            button.removeEventListener('click', handleRemoveFromCart);
            button.addEventListener('click', handleRemoveFromCart);
        });
    }
    // Llama a la función para adjuntar escuchadores cuando el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        attachCartListeners();

        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        document.addEventListener('click', function(event) {
            const cart = document.getElementById('cartFloating');
            const cartButton = document.querySelector('[onclick="toggleCart()"]'); // El botón del carrito en el navbar

            if (cart.style.transform === 'translateX(0%)') {
                // Y el clic no es dentro del carrito ni en el botón de abrir/cerrar
                if (!cart.contains(event.target) && (!cartButton || !cartButton.contains(event.target))) {
                    toggleCart(); // Cierra el carrito
                }
            }
        });
    });
    async function updateCartFloatingComponent() {
        try {
            const response = await fetch('/cart/component');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            const cartFloatingElement = document.getElementById('cartFloating');
            if (cartFloatingElement) {
                const wasOpen = cartFloatingElement.style.transform === 'translateX(0%)';

                // Paso 1: Crea un elemento temporal para poder manipular el HTML recibido
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html; // Esto convierte el string HTML en elementos DOM temporales

                // Paso 2: Encuentra el input del token CSRF dentro del HTML recibido
                const newTokenInput = tempDiv.querySelector('input[name="_token"]');

                // Paso 3: Si se encontró el input del token
                if (newTokenInput) {
                    // Obtiene el token CSRF global de la página principal (el que está en el <head>)
                    const globalCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    // Actualiza el valor del token en el HTML que vas a inyectar con el token global
                    newTokenInput.value = globalCsrfToken;
                }

                // Paso 4: Ahora sí, reemplaza el outerHTML del elemento #cartFloating
                // Usamos tempDiv.innerHTML para obtener el HTML ya modificado con el token correcto
                cartFloatingElement.outerHTML = tempDiv.innerHTML;

                const newCartFloatingElement = document.getElementById('cartFloating');
                if (newCartFloatingElement && wasOpen) {
                    newCartFloatingElement.style.transform = 'translateX(0%)';
                    newCartFloatingElement.classList.remove('hidden');
                }
                attachCartListeners(); // ¡Volver a adjuntar oyentes después de reemplazar el HTML!
            }
        } catch (error) {
            console.error("Error al actualizar el componente del carrito:", error);
        }
    }
</script>