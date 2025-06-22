@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Título dinámico basado en el tipo de tienda actual --}}
    <h1 class="text-3xl font-extrabold mb-8 text-[var(--bluey-dark)] text-center" id="page-title">
        Nuestros Productos para tu {{ ucfirst($storeType ?? 'Mascota') }}
    </h1>

    {{-- Alerta flotante para el carrito --}}
    <div id="cart-alert"
        class="fixed top-20 right-6 bg-[var(--bluey-light)] text-[var(--bluey-dark)] px-4 py-2 rounded-xl shadow-lg hidden z-[9999] transition-all duration-300">
        Producto agregado al carrito ✔️
    </div>

    {{-- Filtro por Categorías y Barra de Búsqueda --}}
    <div class="mb-10">
        <form id="category-filter-form" class="flex flex-col sm:flex-row items-center gap-4">
            {{-- Campo de Búsqueda --}}
            <div class="flex-grow w-full sm:w-auto">
                <label for="search_query" class="sr-only">Buscar Productos</label>
                <input type="search" name="query" id="search_query" placeholder="Buscar productos..."
                       class="select-none border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[var(--medium-gray)]"
                       value="{{ request('query') }}"> {{-- Mantiene el valor de búsqueda al recargar --}}
            </div>

            {{-- Custom Select de Categorías --}}
            <label for="category_id" class="select-none text-lg font-semibold text-[var(--yellow-dark)]">Filtrar por Categoría:</label>
            <div id="custom-select-wrapper" class="select-none relative w-full sm:w-auto min-w-[200px] z-10">
                <div id="custom-select-display" class="border border-gray-300 rounded-md px-3 py-2 cursor-pointer bg-white flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-[var(--bluey-primary)]">
                    <span class="truncate pr-8" id="selected-category-text">
                        {{-- Muestra la categoría seleccionada o 'Todas las categorías' --}}
                        @php
                            $selectedCategoryName = 'Todas las categorías de ' . ucfirst($currentMainPageCategory->name ?? 'la tienda');
                            if (request('category_id') && request('category_id') != $currentMainPageCategory->id) {
                                $foundCategory = $categories->firstWhere('id', request('category_id'));
                                if ($foundCategory) {
                                    $selectedCategoryName = $foundCategory->name;
                                }
                            }
                        @endphp
                        {{ $selectedCategoryName }}
                    </span>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none">▼</span>
                </div>
                <div id="custom-select-options" class="absolute hidden bg-white border border-gray-300 rounded-md mt-1 w-full max-h-60 overflow-y-auto shadow-lg">
                    {{-- Opción 'Todas las categorías' --}}
                    <div class="custom-option px-3 py-2 cursor-pointer hover:bg-gray-100 {{ (!request('category_id') || request('category_id') == $currentMainPageCategory->id) ? 'bg-gray-100 font-semibold' : '' }}"
                        data-value="{{ $currentMainPageCategory->id }}"
                        data-text="Todas las categorías de {{ ucfirst($currentMainPageCategory->name ?? 'la tienda') }}">
                        Todas las categorías de {{ ucfirst($currentMainPageCategory->name ?? 'la tienda') }}
                    </div>
                    {{-- Opciones de categorías reales --}}
                    @foreach ($categories as $category)
                        @if ($category->id != $currentMainPageCategory->id) {{-- Evita duplicar la categoría principal si ya está en "Todas" --}}
                        <div class="custom-option px-3 py-2 cursor-pointer hover:bg-gray-100 {{ request('category_id') == $category->id ? 'bg-gray-100 font-semibold' : '' }}"
                            data-value="{{ $category->id }}"
                            data-text="{{ $category->name }}">
                            {{ $category->name }}
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <input type="hidden" name="category_id" id="hidden_category_id" value="{{ request('category_id', $currentMainPageCategory->id) }}">

            <button type="submit" class="select-none px-5 py-2 bg-[var(--yellow-dark)] hover:bg-[var(--yellow-primary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200">
                Aplicar Filtro
            </button>
        </form>
    </div>

    {{-- Cuadrícula de productos --}}
    <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($productos as $producto)
        <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden flex flex-col justify-between cursor-pointer product-card-trigger select-none"
             style="border-color: var(--medium-gray);"
             data-product-id="{{ $producto->id }}"
             data-product-name="{{ $producto->name }}"
             data-product-description="{{ $producto->description }}"
             data-product-price="{{ number_format($producto->price, 2, '.', '') }}"
             data-product-image="{{ asset('storage/' . $producto->image) }}"
             data-product-stock="{{ $producto->stock }}"
             data-product-category="{{ $producto->category->name ?? 'N/A' }}"
             @php
                 $activePromotions = $producto->getActivePromotions();
                 $appliedData = $producto->applyPromotions($producto->price, 1, $activePromotions);
                 $finalPrice = number_format($appliedData['final_price_per_unit'], 2, '.', '');
                 $giftQuantity = $appliedData['gift_quantity'];
                 $appliedPromotionTitles = $appliedData['applied_promotion_titles'];
             @endphp
             data-product-promotions='@json($appliedPromotionTitles)'
             data-product-final-price="{{ $finalPrice }}"
             data-product-gift-quantity="{{ $giftQuantity }}">

            <div class="relative overflow-hidden group">
                <img src="{{ asset('storage/' . $producto->image) }}" alt="{{ $producto->name }}"
                    class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105 select-none"
                    loading="lazy">
                @if(!empty($appliedPromotionTitles))
                <span class="absolute top-2 left-2 bg-[var(--yellow-dark)] text-white text-xs font-semibold px-2 py-1 rounded-full shadow select-none">
                    Oferta
                </span>
                @endif
            </div>

            <div class="p-4 flex-grow">
                <h2 class="text-xl font-semibold text-[var(--black)] mb-2 line-clamp-2" title="{{ $producto->name }}">{{ $producto->name }}</h2>
                <p class="text-[var(--dark-gray)] text-sm mb-3 line-clamp-3">{{ $producto->description }}</p>

                @if(!empty($appliedPromotionTitles))
                    @foreach($appliedPromotionTitles as $promoTitle)
                        <p class="text-sm text-[var(--yellow-dark)] font-semibold mb-2">🎁 {{ $promoTitle }}</p>
                    @endforeach
                @endif

                {{-- Mostrar el precio original tachado si hay descuento, y el precio final --}}
                @if($finalPrice < number_format($producto->price, 2, '.', ''))
                    <p class="text-[var(--dark-gray)] text-lg line-through mb-1">S/{{ number_format($producto->price, 2) }}</p>
                    <p class="text-[var(--bluey-dark)] text-2xl font-bold mb-4">S/{{ $finalPrice }}</p>
                @else
                    <p class="text-[var(--bluey-dark)] text-2xl font-bold mb-4">S/{{ number_format($producto->price, 2) }}</p>
                @endif
            </div>

            <div class="p-4 border-t border-[var(--medium-gray)]">
                <button
                    class="add-to-cart-btn w-full px-4 py-2 bg-[var(--bluey-primary)] hover:bg-[var(--bluey-secondary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200"
                    data-id="{{ $producto->id }}">
                    🛒 Agregar al carrito
                </button>
            </div>
        </div>
        @empty
        <div id="no-products-message" class="col-span-full text-center text-gray-600 text-xl py-10">
            No se encontraron productos en esta categoría.
        </div>
        @endforelse
    </div>
    <div id="loading-spinner" class="text-center py-8 hidden">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[var(--bluey-primary)] mx-auto"></div>
        <p class="text-gray-600 mt-2">Cargando productos...</p>
    </div>
</div>

{{-- Modal --}}
<div id="product-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-[10000] hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-2xl max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300 relative">
        <button id="close-modal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-2xl font-bold">✖</button>

        <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-1/2">
                <img id="modal-product-image" src="" alt="Imagen del producto" class="w-full h-auto object-cover rounded-md mb-4">
            </div>
            <div class="md:w-1/2">
                <h2 id="modal-product-name" class="text-3xl font-extrabold text-[var(--bluey-dark)] mb-2"></h2>
                <p id="modal-product-category" class="text-sm text-[var(--dark-gray)] mb-2"></p>

                <div id="modal-product-promotions" class="mb-3">
                    {{-- Aquí se insertarán las promociones dinámicamente --}}
                </div>

                <p id="modal-product-price" class="text-[var(--bluey-dark)] text-3xl font-bold mb-4"></p>
                <p id="modal-product-description" class="text-[var(--dark-gray)] text-base mb-4 max-h-60 overflow-y-auto"></p>
                <p id="modal-product-stock" class="text-[var(--dark-gray)] text-sm mb-4"></p>

                <button id="modal-add-to-cart-btn"
                    class="add-to-cart-btn w-full px-4 py-3 bg-[var(--bluey-primary)] hover:bg-[var(--bluey-secondary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200"
                    data-id="">
                    🛒 Agregar al carrito
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    // PASO CLAVE PARA LA URL BASE: Blade imprimirá el ID principal aquí
    const CURRENT_MAIN_CATEGORY_ID = {{ $currentMainPageCategory->id }};
    const CURRENT_MAIN_CATEGORY_NAME = "{{ addslashes(ucfirst($storeType ?? 'Mascota')) }}";

    // MODIFICACIÓN CLAVE: Determinar la URL base para las peticiones AJAX de productos
    const BASE_AJAX_PRODUCTS_URL = CURRENT_MAIN_CATEGORY_ID == 1
        ? "{{ url('/productos/petshop') }}"
        : "{{ url('/productos/categoria') }}" + "/" + CURRENT_MAIN_CATEGORY_ID;

    // --- Script para la alerta del carrito (Puede ser global sin problema) ---
    function showCartAlert(message = 'Producto agregado al carrito ✔️', isSuccess = true) {
        const alert = document.getElementById('cart-alert');
        alert.textContent = message;
        alert.classList.remove('hidden', 'bg-[var(--bluey-light)]', 'bg-red-200', 'text-[var(--bluey-dark)]', 'text-red-800');
        alert.classList.add('opacity-100');

        if (isSuccess) {
            alert.classList.add('bg-[var(--bluey-light)]', 'text-[var(--bluey-dark)]');
        } else {
            alert.classList.add('bg-red-200', 'text-red-800');
        }

        setTimeout(() => {
            alert.classList.add('hidden');
            alert.classList.remove('opacity-100');
        }, 2500);
    }

    // --- MANEJO DE ADD TO CART (DELEGACIÓN DE EVENTOS - Global porque escucha clics en todo el documento) ---
    document.addEventListener('click', async function(event) {
        if (event.target.classList.contains('add-to-cart-btn')) {
            const productId = event.target.dataset.id;

            try {
                const res = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quantity: 1 // Por defecto, se agrega 1 unidad
                    })
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count || 0;
                    }
                    showCartAlert(data.message, true);

                    // Asegúrate de que closeModal exista antes de llamarla
                    if (event.target.id === 'modal-add-to-cart-btn' && typeof closeModal === 'function') {
                        closeModal();
                    }

                    if (typeof updateCartFloatingComponent === 'function') {
                        await updateCartFloatingComponent();
                    }

                } else {
                    const errorMessage = data.message || 'Error al agregar el producto al carrito.';
                    showCartAlert(errorMessage, false);
                }
            } catch (err) {
                console.error('Error adding to cart:', err);
                showCartAlert('Hubo un error de conexión al agregar el producto.', false);
            }
        }
    });

    // --- Envuelve toda la lógica que interactúa con el DOM dentro de DOMContentLoaded ---
    document.addEventListener('DOMContentLoaded', function() {
        // --- Variables y Funciones del Modal (Inicializadas aquí para asegurar que el DOM esté listo) ---
        const productModal = document.getElementById('product-modal');
        const closeModalBtn = document.getElementById('close-modal');
        const modalProductName = document.getElementById('modal-product-name');
        const modalProductCategory = document.getElementById('modal-product-category');
        const modalProductPromotions = document.getElementById('modal-product-promotions');
        const modalProductPrice = document.getElementById('modal-product-price');
        const modalProductDescription = document.getElementById('modal-product-description');
        const modalProductImage = document.getElementById('modal-product-image');
        const modalProductStock = document.getElementById('modal-product-stock');
        const modalAddToCartBtn = document.getElementById('modal-add-to-cart-btn');

        // Define openModal y closeModal aquí, porque usan las variables del DOM inicializadas arriba
        window.openModal = function(product) { // Hacemos openModal global explícitamente si se necesita fuera
            modalProductName.textContent = product.name;
            modalProductCategory.textContent = `Categoría: ${product.category}`;
            modalProductDescription.textContent = product.description;
            modalProductPrice.textContent = `S/.${parseFloat(product.final_price || product.price).toFixed(2)}`;
            modalProductImage.src = product.image;
            modalProductStock.textContent = `Stock disponible: ${product.stock}`;
            modalAddToCartBtn.dataset.id = product.id;

            modalProductPromotions.innerHTML = '';
            try {
                const promotions = product.promotions;
                if (Array.isArray(promotions) && promotions.length > 0) {
                    promotions.forEach(promoTitle => {
                        const p = document.createElement('p');
                        p.className = 'text-sm text-[var(--yellow-dark)] font-semibold mb-1';
                        p.textContent = `🎁 ${promoTitle}`;
                        modalProductPromotions.appendChild(p);
                    });
                }
            } catch (e) {
                console.error('Error parsing promotions for modal:', e);
            }

            productModal.classList.remove('hidden', 'opacity-0');
            productModal.classList.add('opacity-100');
            productModal.querySelector('div').classList.remove('scale-95');
            productModal.querySelector('div').classList.add('scale-100');
            document.body.style.overflow = 'hidden';
        };

        window.closeModal = function() { // Hacemos closeModal global explícitamente
            productModal.classList.remove('opacity-100');
            productModal.classList.add('opacity-0');
            productModal.querySelector('div').classList.remove('scale-100');
            productModal.querySelector('div').classList.add('scale-95');

            setTimeout(() => {
                productModal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        };

        // --- Event Listeners del Modal ---
        closeModalBtn.addEventListener('click', closeModal);

        productModal.addEventListener('click', function(event) {
            if (event.target === productModal) {
                closeModal();
            }
        });

        // Delegación de eventos para las tarjetas de producto (abrir modal)
        document.getElementById('products-grid').addEventListener('click', function(event) {
            const card = event.target.closest('.product-card-trigger');

            if (card && !event.target.closest('.add-to-cart-btn')) {
                const product = {
                    id: card.dataset.productId,
                    name: card.dataset.productName,
                    description: card.dataset.productDescription,
                    price: card.dataset.productPrice,
                    final_price: card.dataset.productFinalPrice,
                    image: card.dataset.productImage,
                    stock: card.dataset.productStock,
                    promotions: JSON.parse(card.dataset.productPromotions || '[]'),
                    category: card.dataset.productCategory
                };
                openModal(product);
            }
        });

        // --- SCRIPT PARA EL SELECT PERSONALIZADO (ADAPTADO PARA AJAX) ---
        const customSelectWrapper = document.getElementById('custom-select-wrapper');
        const customSelectDisplay = document.getElementById('custom-select-display');
        const customSelectOptions = document.getElementById('custom-select-options');
        const selectedCategoryText = document.getElementById('selected-category-text');
        const hiddenCategoryId = document.getElementById('hidden_category_id');
        const categoryFilterForm = document.getElementById('category-filter-form');
        const searchInput = document.getElementById('search_query');

        customSelectDisplay.addEventListener('click', function() {
            customSelectOptions.classList.toggle('hidden');
        });

        customSelectOptions.querySelectorAll('.custom-option').forEach(option => {
            option.addEventListener('click', function() {
                const value = this.dataset.value;
                const text = this.dataset.text;

                selectedCategoryText.textContent = text;
                hiddenCategoryId.value = value;
                customSelectOptions.classList.add('hidden');
                fetchProducts();
            });
        });

        document.addEventListener('click', function(event) {
            if (!customSelectWrapper.contains(event.target)) {
                customSelectOptions.classList.add('hidden');
            }
        });

        categoryFilterForm.addEventListener('submit', function(event) {
            event.preventDefault();
            fetchProducts();
        });

        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                fetchProducts();
            }
        });

        // --- NUEVAS FUNCIONES PARA CARGA AJAX DE PRODUCTOS (Estas no necesitan DOMContentLoaded, pero es mejor que las llamemos desde aquí) ---

        // Función principal para obtener y mostrar productos con AJAX
        // La hacemos global para que pueda ser llamada desde cualquier parte
        window.fetchProducts = async function() {
            const productsGrid = document.getElementById('products-grid');
            const noProductsMessage = document.getElementById('no-products-message');
            const loadingSpinner = document.getElementById('loading-spinner');
            const hiddenCategoryId = document.getElementById('hidden_category_id');
            const searchInput = document.getElementById('search_query');
            const pageTitle = document.getElementById('page-title');
            const selectedCategoryText = document.getElementById('selected-category-text');

            const categoryId = hiddenCategoryId.value;
            const searchQuery = searchInput.value;

            const url = new URL(BASE_AJAX_PRODUCTS_URL);

            url.searchParams.set('category_id', categoryId);
            if (searchQuery) {
                url.searchParams.set('query', searchQuery);
            }
            url.searchParams.set('ajax', 'true');

            productsGrid.innerHTML = '';
            if (noProductsMessage) noProductsMessage.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.displaying_category_name) {
                    if (categoryId == CURRENT_MAIN_CATEGORY_ID) {
                        pageTitle.textContent = `Nuestros Productos para tu ${CURRENT_MAIN_CATEGORY_NAME}`;
                        selectedCategoryText.textContent = `Todas las categorías de ${CURRENT_MAIN_CATEGORY_NAME}`;
                    } else {
                        const selectedCatObj = data.categories.find(cat => cat.id == categoryId);
                        if (selectedCatObj) {
                            selectedCategoryText.textContent = selectedCatObj.name;
                        }
                    }
                }

                if (data.products && data.products.length > 0) {
                    data.products.forEach(product => {
                        productsGrid.insertAdjacentHTML('beforeend', renderProductCard(product));
                    });
                } else {
                    productsGrid.innerHTML = `
                        <div class="col-span-full text-center text-gray-600 text-xl py-10" id="no-products-message">
                            No se encontraron productos en esta categoría.
                        </div>
                    `;
                }

            } catch (error) {
                console.error('Error fetching products:', error);
                productsGrid.innerHTML = `
                    <div class="col-span-full text-center text-red-600 text-xl py-10" id="error-message">
                        Hubo un error al cargar los productos. Por favor, inténtalo de nuevo.
                    </div>
                `;
            } finally {
                loadingSpinner.classList.add('hidden');
            }
        };

        // Función para renderizar una sola tarjeta de producto (Puede ser global)
        window.renderProductCard = function(product) { // La hacemos global también
            const hasPromotions = product.applied_promotions && product.applied_promotions.length > 0;
            const originalPriceHtml = (hasPromotions && parseFloat(product.final_price) < parseFloat(product.price))
                ? `<p class="text-[var(--dark-gray)] text-lg line-through mb-1">S/.${parseFloat(product.price).toFixed(2)}</p>`
                : '';
            const displayPrice = parseFloat(product.final_price || product.price).toFixed(2);

            const dataProductPromotions = JSON.stringify(product.applied_promotions || []);

            return `
                <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden flex flex-col justify-between cursor-pointer product-card-trigger select-none"
                     style="border-color: var(--medium-gray);"
                     data-product-id="${product.id}"
                     data-product-name="${product.name}"
                     data-product-description="${product.description}"
                     data-product-price="${parseFloat(product.price).toFixed(2)}"
                     data-product-image="${product.image_url}"
                     data-product-stock="${product.stock}"
                     data-product-promotions='${dataProductPromotions}'
                     data-product-category="${product.category_name}"
                     data-product-final-price="${displayPrice}"
                     data-product-gift-quantity="${product.gift_quantity || 0}">

                    <div class="relative overflow-hidden group">
                        <img src="${product.image_url}" alt="${product.name}"
                             class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105 select-none"
                             loading="lazy">
                        ${hasPromotions ? `<span class="absolute top-2 left-2 bg-[var(--yellow-dark)] text-white text-xs font-semibold px-2 py-1 rounded-full shadow select-none">Oferta</span>` : ''}
                    </div>

                    <div class="p-4 flex-grow">
                        <h2 class="text-xl font-semibold text-[var(--black)] mb-2 line-clamp-2" title="${product.name}">${product.name}</h2>
                        <p class="text-[var(--dark-gray)] text-sm mb-3 line-clamp-3">${product.description}</p>

                        ${hasPromotions ? product.applied_promotions.map(promoTitle => `
                            <p class="text-sm text-[var(--yellow-dark)] font-semibold mb-2">🎁 ${promoTitle}</p>
                        `).join('') : ''}

                        ${originalPriceHtml}
                        <p class="text-[var(--bluey-dark)] text-2xl font-bold mb-4">S/.${displayPrice}</p>
                    </div>

                    <div class="p-4 border-t border-[var(--medium-gray)]">
                        <button
                            class="add-to-cart-btn w-full px-4 py-2 bg-[var(--bluey-primary)] hover:bg-[var(--bluey-secondary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200"
                            data-id="${product.id}">
                            🛒 Agregar al carrito
                        </button>
                    </div>
                </div>
            `;
        };
    }); // Fin de DOMContentLoaded
</script>
@endsection