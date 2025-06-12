@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Título dinámico basado en el tipo de tienda actual --}}
    <h1 class="text-3xl font-extrabold mb-8 text-[var(--bluey-dark)] text-center">
        Nuestros Productos para tu {{ ucfirst($storeType ?? 'Mascota') }}
    </h1>

    {{-- Alerta flotante para el carrito --}}
    <div id="cart-alert"
        class="fixed top-20 right-6 bg-[var(--bluey-light)] text-[var(--bluey-dark)] px-4 py-2 rounded-xl shadow-lg hidden z-[9999] transition-all duration-300">
        Producto agregado al carrito ✔️
    </div>

    {{-- Filtro por Categorías y Barra de Búsqueda --}}
    <div class="mb-10">
        {{-- Mantenemos el action y method del formulario --}}
        <form action="{{ route('productos.por_categoria', ['id' => $currentMainPageCategory->id]) }}" method="GET" id="category-filter-form" class="flex flex-col sm:flex-row items-center gap-4">
            {{-- Campo de Búsqueda AÑADIDO AQUÍ --}}
            <div class="flex-grow w-full sm:w-auto">
                <label for="search_query" class="sr-only">Buscar Productos</label>
                <input type="search" name="query" id="search_query" placeholder="Buscar productos..."
                       class="select-none border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[var(--medium-gray)]"
                       value="{{ request('query') }}"> {{-- Mantiene el valor de búsqueda al recargar --}}
            </div>

            {{-- Custom Select de Categorías (EXISTENTE) --}}
            <label for="category_id" class="select-none text-lg font-semibold text-[var(--yellow-dark)]">Filtrar por Categoría:</label>
            <div id="custom-select-wrapper" class=" select-none relative w-full sm:w-auto min-w-[200px] z-10">
                <div id="custom-select-display" class="border border-gray-300 rounded-md px-3 py-2 cursor-pointer bg-white flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-[var(--bluey-primary)]">
                    <span class="truncate pr-8" id="selected-category-text">
                        {{ !request('category_id') || request('category_id') == $currentMainPageCategory->id
                            ? 'Todas las categorías de ' . ucfirst($currentMainPageCategory->name ?? 'la tienda')
                            : ($categories->firstWhere('id', request('category_id'))->name ?? 'Selecciona una categoría') }}
                    </span>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none">▼</span>
                </div>
                <div id="custom-select-options" class="absolute hidden bg-white border border-gray-300 rounded-md mt-1 w-full max-h-60 overflow-y-auto shadow-lg">
                    <div class="custom-option px-3 py-2 cursor-pointer hover:bg-gray-100 {{ (!request('category_id') || request('category_id') == $currentMainPageCategory->id) ? 'bg-gray-100 font-semibold' : '' }}"
                        data-value="{{ $currentMainPageCategory->id }}"
                        data-text="Todas las categorías de {{ ucfirst($currentMainPageCategory->name ?? 'la tienda') }}">
                        Todas las categorías de {{ ucfirst($currentMainPageCategory->name ?? 'la tienda') }}
                    </div>
                    @foreach ($categories as $category)
                        @if ($category->id != $currentMainPageCategory->id)
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

            <button type="submit" class="select-none  px-5 py-2 bg-[var(--yellow-dark)] hover:bg-[var(--yellow-primary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200">
                Aplicar Filtro
            </button>
        </form>
    </div>

    {{-- Cuadrícula de productos (existente) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($productos as $producto)
        <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden flex flex-col justify-between cursor-pointer product-card-trigger select-none"
             style="border-color: var(--medium-gray);"
             data-product-id="{{ $producto->id }}"
             data-product-name="{{ $producto->name }}"
             data-product-description="{{ $producto->description }}"
             data-product-price="{{ number_format($producto->price, 2) }}"
             data-product-image="{{ asset('storage/' . $producto->image) }}"
             data-product-stock="{{ $producto->stock }}"
             data-product-promotions="{{ $producto->promotions->pluck('title')->toJson() }}"
             data-product-category="{{ $producto->category->name ?? 'N/A' }}">

            <div class="relative overflow-hidden group">
                <img src="{{ asset('storage/' . $producto->image) }}" alt="{{ $producto->name }}"
                    class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105 select-none">
                @if($producto->promotions->isNotEmpty())
                <span class="absolute top-2 left-2 bg-[var(--yellow-dark)] text-white text-xs font-semibold px-2 py-1 rounded-full shadow select-none">
                    Oferta
                </span>
                @endif
            </div>

            <div class="p-4 flex-grow">
                <h2 class="text-xl font-semibold text-[var(--black)] mb-2 line-clamp-2" title="{{ $producto->name }}">{{ $producto->name }}</h2>
                <p class="text-[var(--dark-gray)] text-sm mb-3 line-clamp-3">{{ $producto->description }}</p>

                @if($producto->promotions->isNotEmpty())
                @foreach($producto->promotions as $promotion)
                <p class="text-sm text-[var(--yellow-dark)] font-semibold mb-2">🎁 {{ $promotion->title }}</p>
                @endforeach
                @endif

                <p class="text-[var(--bluey-dark)] text-2xl font-bold mb-4">${{ number_format($producto->price, 2) }}</p>
            </div>

            <div class="p-4 border-t border-[var(--medium-gray)]">
                <button
                    class="add-to-cart-btn w-full px-4 py-2 bg-[var(--bluey-primary)] hover:bg-[var(--bluey-secondary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200"
                    data-id="{{ $producto->id }}"
                    onclick="event.stopPropagation()">
                    🛒 Agregar al carrito
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center text-gray-600 text-xl py-10">
            No se encontraron productos en esta categoría.
        </div>
        @endforelse
    </div>
</div>

{{-- Modal (existente) --}}
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
                    class="w-full px-4 py-3 bg-[var(--bluey-primary)] hover:bg-[var(--bluey-secondary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200"
                    data-id="">
                    🛒 Agregar al carrito
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Script para la alerta del carrito ---
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

    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const productId = this.dataset.id;

            try {
                const res = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quantity: 1
                    })
                });
                const data = await res.json();

                if (data.success) {
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count || 0;
                    }

                    showCartAlert(data.message, true);

                    if (typeof updateCartFloatingComponent === 'function') {
                        await updateCartFloatingComponent();
                    }
                } else {
                    showCartAlert(data.message || 'Error al agregar al carrito.', false);
                }
            } catch (err) {
                console.error(err);
                showCartAlert('Hubo un error de conexión al agregar el producto.', false);
            }
        });
    });

    // --- SCRIPT PARA EL SELECT PERSONALIZADO ---
    document.addEventListener('DOMContentLoaded', function() {
        const customSelectWrapper = document.getElementById('custom-select-wrapper');
        const customSelectDisplay = document.getElementById('custom-select-display');
        const customSelectOptions = document.getElementById('custom-select-options');
        const selectedCategoryText = document.getElementById('selected-category-text');
        const hiddenCategoryId = document.getElementById('hidden_category_id');
        const categoryFilterForm = document.getElementById('category-filter-form'); 

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
            });
        });

        document.addEventListener('click', function(event) {
            if (!customSelectWrapper.contains(event.target)) {
                customSelectOptions.classList.add('hidden');
            }
        });
    });

    // --- SCRIPT PARA EL MODAL DE PRODUCTO ---
    document.addEventListener('DOMContentLoaded', function() {
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

        function openModal(product) {
            modalProductName.textContent = product.name;
            modalProductCategory.textContent = `Categoría: ${product.category}`;
            modalProductDescription.textContent = product.description;
            modalProductPrice.textContent = `$${product.price}`;
            modalProductImage.src = product.image;
            modalProductStock.textContent = `Stock disponible: ${product.stock}`;
            modalAddToCartBtn.dataset.id = product.id;

            modalProductPromotions.innerHTML = '';
            try {
                const promotions = JSON.parse(product.promotions);
                if (Array.isArray(promotions) && promotions.length > 0) {
                    promotions.forEach(promoTitle => {
                        const p = document.createElement('p');
                        p.className = 'text-sm text-[var(--yellow-dark)] font-semibold mb-1';
                        p.textContent = `🎁 ${promoTitle}`;
                        modalProductPromotions.appendChild(p);
                    });
                }
            } catch (e) {
                console.error('Error parsing promotions JSON:', e);
            }

            productModal.classList.remove('hidden', 'opacity-0');
            productModal.classList.add('opacity-100');
            productModal.querySelector('div').classList.remove('scale-95');
            productModal.querySelector('div').classList.add('scale-100');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            productModal.classList.remove('opacity-100');
            productModal.classList.add('opacity-0');
            productModal.querySelector('div').classList.remove('scale-100');
            productModal.querySelector('div').classList.add('scale-95');
            
            setTimeout(() => {
                productModal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300); 
        }

        closeModalBtn.addEventListener('click', closeModal);

        productModal.addEventListener('click', function(event) {
            if (event.target === productModal) {
                closeModal();
            }
        });

        document.querySelectorAll('.product-card-trigger').forEach(card => {
            card.addEventListener('click', function(event) {
                if (event.target.closest('.add-to-cart-btn')) {
                    return; 
                }

                const product = {
                    id: this.dataset.productId,
                    name: this.dataset.productName,
                    description: this.dataset.productDescription,
                    price: this.dataset.productPrice,
                    image: this.dataset.productImage,
                    stock: this.dataset.productStock,
                    promotions: this.dataset.productPromotions,
                    category: this.dataset.productCategory
                };
                openModal(product);
            });
        });

        modalAddToCartBtn.addEventListener('click', async function() {
            const productId = this.dataset.id;
            
            try {
                const res = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quantity: 1
                    })
                });
                const data = await res.json();

                if (data.success) {
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count || 0;
                    }
                    if (typeof showCartAlert === 'function') {
                        showCartAlert(data.message, true);
                    }
                    closeModal();
                    if (typeof updateCartFloatingComponent === 'function') {
                        await updateCartFloatingComponent();
                    }
                } else {
                    if (typeof showCartAlert === 'function') {
                        showCartAlert(data.message || 'Error al agregar al carrito.', false);
                    }
                }
            } catch (err) {
                console.error(err);
                if (typeof showCartAlert === 'function') {
                    showCartAlert('Hubo un error de conexión al agregar el producto.', false);
                }
            }
        });
    });
</script>
@endsection