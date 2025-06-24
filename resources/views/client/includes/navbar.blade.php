<nav class="navbar fixed top-0 left-0 w-full bg-white shadow-md z-40">
    {{-- Componente de Notificación Flotante (sin cambios aquí) --}}
    @if (session('status'))
    <div x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 3000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-full"
        class="fixed bottom-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white text-sm font-semibold
                    @if (session('status')) bg-bluey-dark @endif">
        {{ session('status') }}
        <button @click="show = false" class="ml-2 text-white hover:text-gray-100 focus:outline-none">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    <div class="navbar-container flex justify-between items-center h-16 px-4 md:px-6 lg:px-8">

        {{-- Logo (sin cambios aquí) --}}
        <a href="{{ url('/') }}" class="logo flex items-center flex-shrink-0 space-x-2">
            <img src="{{ asset('img/logo-blueyvet.png') }}" alt="BlueyVet" class="logo-img h-10 w-auto">
            <span class="logo-text text-bluey-dark text-2xl font-bold hidden sm:inline">BLUEYVET</span>
        </a>

        {{-- Botón de Hamburguesa para el menú principal (solo en móviles) --}}
        <div class="md:hidden flex-grow flex justify-end" x-data="{ mobileMenuOpen: false }">
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                class="text-bluey-dark hover:text-bluey-primary focus:outline-none focus:ring-2 focus:ring-bluey-primary rounded-md p-2">
                <i class="bi bi-list text-3xl"></i>
            </button>
        </div>

        {{-- Menú de Navegación (oculto en móviles, visible en md y arriba) --}}
        <div id="main-menu" class="hidden md:flex flex-grow justify-center items-center space-x-6 lg:space-x-8">
            <div class="dropdown relative group">
                <button class="menu-link dropdown-toggle flex items-center">
                    PRODUCTOS <i class="bi bi-chevron-down dropdown-icon ml-1 text-sm"></i>
                </button>
                <div class="dropdown-menu absolute hidden group-hover:block bg-white shadow-lg rounded-md py-1 mt-0 w-40 z-48">
                    <a href="{{ route('productos.por_categoria', ['id' => 2]) }}" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark">
                        <i class="bi bi-capsule mr-2"></i> Farmacia
                    </a>
                    <a href="{{ route('productos.por_categoria', ['id' => 1]) }}" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark">
                        <i class="bi bi-bag-heart mr-2"></i> Petshop
                    </a>
                </div>
            </div>

            <a href="{{ url('/') }}" class="menu-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="bi bi-house mr-1"></i> INICIO
            </a>
            <a href="{{ route('blog.index') }}" class="menu-link">
                <i class="bi bi-newspaper mr-1"></i> BLOG
            </a>

            <a href="{{ route('client.servicios.index') }}" class="menu-link">
                <i class="bi bi-heart-pulse mr-1"></i> SERVICIOS
            </a>
            <a href="{{ route('client.mascotas.index') }}" class="menu-link">
                <i class="bi bi-clipboard-heart mr-1"></i> MASCOTAS
            </a>
            <a href="{{ route('client.citas.index') }}" class="menu-link">
                <i class="bi bi-calendar-check mr-1"></i> CITAS
            </a>
        </div>

        {{-- Menú de Acción (Carrito, Notificaciones y Perfil) (sin cambios aquí) --}}
        <div class="action-icons flex items-center space-x-3 ml-auto md:ml-6">

            {{-- Ícono de Carrito --}}
            <div id="carrito-icono" class="icon-btn cart-icon" title="Carrito" onclick="toggleCart()">
                <i class="bi bi-cart text-lg md:text-lg"></i>
                <span id="cart-count" class="cart-badge">{{ count(session('cart', [])) }}</span>
            </div>
            <x-cart-floating :cart="$cart ?? []" :total="$total ?? 0" />

            {{-- Menú de Notificaciones (sin cambios aquí en el botón principal) --}}
            <div x-data="{ notificationOpen: false }" class="relative z-30">
                <button @click="notificationOpen = !notificationOpen"
                    @click.outside="notificationOpen = false"
                    type="button"
                    class="relative p-1 text-gray-700 hover:text-bluey-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bluey-primary rounded-full">
                    <span class="sr-only">Ver notificaciones</span>
                    <i class="bi bi-bell text-lg md:text-lg"></i>
                    @auth
                    @if (Auth::user()->unreadNotifications->count() > 0)
                    <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full transform translate-x-1/2 -translate-y-1/2">
                        {{ Auth::user()->unreadNotifications->count() }}
                    </span>
                    @endif
                    @endauth
                </button>

                <div x-show="notificationOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-80 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                    <div class="px-4 py-2 border-b border-gray-200 text-sm font-semibold text-gray-800">
                        Notificaciones
                    </div>
                    @auth
                    @forelse (Auth::user()->unreadNotifications->take(5) as $notification)
                    {{-- Contenedor flex para la notificación y el botón de eliminar --}}
                    <div class="px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100 flex justify-between items-start">
                        <div> {{-- Contenido de la notificación --}}
                            <p class="font-semibold">{{ $notification->data['title'] ?? 'Nueva notificación' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($notification->data['body'] ?? $notification->type, 50) }}</p>
                            <span class="text-xs text-gray-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>

                            @if(isset($notification->data['type']) && $notification->data['type'] === 'reprogramacion' && !empty($notification->data['reprogramming_request_id']))
                            <form action="{{ route('notifications.reprogramacion.aceptar', $notification->data['reprogramming_request_id']) }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">
                                    Aceptar reprogramación
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- FORMULARIO PARA ELIMINAR NOTIFICACIÓN INDIVIDUAL --}}
                        {{-- Añadimos margen a la izquierda y un tamaño más pequeño para el ícono --}}
                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta notificación?');" class="ml-2 flex-shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-500 text-xs p-1 rounded hover:bg-gray-200">
                                <i class="bi bi-trash-fill"></i> {{-- Usamos trash-fill para que sea más visible --}}
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="px-4 py-3 text-sm text-gray-500">No tienes notificaciones nuevas.</p>
                    @endforelse

                    {{-- Pie del menú de notificaciones (Ver todas) --}}
                    @if (Auth::user()->notifications->count() > 0)
                    <div class="border-t border-gray-200 mt-1">
                        <a href="{{ route('notifications.index') }}" class="block text-center py-2 text-sm text-bluey-primary hover:bg-gray-50">Ver todas las notificaciones</a>
                    </div>
                    @endif
                    @else
                    <p class="px-4 py-3 text-sm text-gray-500">Inicia sesión para ver tus notificaciones.</p>
                    @endauth
                </div>
            </div>
            {{-- Menú de Usuario/Perfil unificado (sin cambios aquí) --}}
            <div x-data="{ userMenuOpen: false, showTooltip: false }" class="relative z-30">
                <button @click="userMenuOpen = !userMenuOpen"
                    @click.outside="userMenuOpen = false"
                    @mouseenter="showTooltip = true"
                    @mouseleave="showTooltip = false"
                    class="flex items-center space-x-1 text-bluey-dark hover:text-bluey-primary focus:outline-none px-1 py-1 rounded-md transition-colors duration-200"
                    id="user-profile-menu-button" aria-expanded="userMenuOpen" aria-haspopup="true">
                    @auth
                    <span class="relative">
                        <span class="text-sm font-semibold hidden lg:inline">{{ __('Hola,') }} {{ Str::limit(Auth::user()->name, 10, '...') }}</span>
                        <!-- <span class="text-bluey-dark font-bold text-lg p-2 rounded-full bg-bluey-light hidden md:inline lg:hidden">{{ substr(Auth::user()->name, 0, 1) }}</span> -->
                    </span>
                    <span class="relative">
                        <i class="bi bi-person text-xl md:text-2xl"></i>
                        @if (Auth::user()->needsProfileCompletion)
                        <span class="absolute top-0 right-0 w-1 h-1 bg-red-500 rounded-full animate-ping-once-small"></span>
                        @endif
                    </span>
                    <i class="bi bi-chevron-down text-xs ml-1 transition-transform duration-200 ease-in-out"
                        :class="{'rotate-180': userMenuOpen}"></i>
                    @else
                    <span class="text-sm font-semibold hidden lg:inline">{{ __('Mi Cuenta') }}</span>
                    <i class="bi bi-person text-xl md:text-2xl"></i>
                    <i class="bi bi-chevron-down text-xs ml-1 transition-transform duration-200 ease-in-out"
                        :class="{'rotate-180': userMenuOpen}"></i>
                    @endauth
                </button>

                @auth
                @if (Auth::user()->needsProfileCompletion)
                <div x-show="showTooltip && !userMenuOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute bottom-full right-0 mb-2 p-2 text-xs text-white bg-gray-800 rounded shadow-lg whitespace-nowrap z-40 origin-bottom-right">
                    Completa tu perfil para una mejor experiencia.
                    <div class="absolute w-2 h-2 bg-gray-800 transform rotate-45 -bottom-1 right-3"></div>
                </div>
                @endif
                @endauth

                <div x-show="userMenuOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none origin-top-right">
                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="user-profile-menu-button">
                        @auth
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark flex items-center" role="menuitem">
                            <i class="bi bi-person-fill mr-2 "></i> {{ __('Mi Perfil') }}
                            @if (Auth::user()->needsProfileCompletion)
                            <span class="ml-auto w-2 h-2 bg-red-500 rounded-full animate-ping-once-small"></span>
                            @endif
                        </a>
                        <a href="{{ route('ClientOrders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark flex items-center" role="menuitem">
                            <i class="bi-box-seam mr-2 "></i> {{ __('Pedidos') }}
                            @if (Auth::user()->needsProfileCompletion)
                            <span class="ml-auto w-2 h-2 bg-red-500 rounded-full animate-ping-once-small"></span>
                            @endif
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-100 hover:text-red-800" role="menuitem">
                                <i class="bi bi-box-arrow-right mr-2"></i> {{ __('Cerrar Sesión') }}
                            </button>
                        </form>
                        @else
                        <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark" role="menuitem">
                            <i class="bi bi-box-arrow-in-right mr-2"></i> {{ __('Iniciar Sesión') }}
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Menú móvil desplegable --}}
    <div id="mobile-menu" x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden absolute top-16 left-0 w-full bg-white shadow-lg py-2 z-20">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <div x-data="{ mobileProductOpen: false }" class="relative px-2">
                <button @click="mobileProductOpen = !mobileProductOpen"
                    class="menu-link w-full text-left flex items-center justify-between py-2 px-3 rounded-md hover:bg-bluey-light hover:text-bluey-dark **text-base font-medium**">
                    PRODUCTOS <i class="bi bi-chevron-down dropdown-icon ml-1 text-sm" :class="{'rotate-180': mobileProductOpen}"></i>
                </button>
                <div x-show="mobileProductOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="**pl-4 pt-1 pb-2**"> {{-- Quitamos `dropdown-menu` de aquí --}}
                    <a href="{{ route('productos.por_categoria', ['id' => 2]) }}" class="**menu-link block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark font-medium**">
                        <i class="bi bi-capsule mr-2"></i> Farmacia
                    </a>
                    <a href="{{ route('productos.por_categoria', ['id' => 1]) }}" class="**menu-link block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark font-medium**">
                        <i class="bi bi-bag-heart mr-2"></i> Petshop
                    </a>
                </div>
            </div>

            <a href="{{ url('/') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'active' : '' }} hover:bg-bluey-light hover:text-bluey-dark">
                <i class="bi bi-house mr-2"></i> INICIO
            </a>
            <a href="{{ route('blog.index') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium hover:bg-bluey-light hover:text-bluey-dark">
                <i class="bi bi-newspaper mr-2"></i> BLOG
            </a>

            <a href="{{ route('client.servicios.index') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium hover:bg-bluey-light hover:text-bluey-dark">
                <i class="bi bi-heart-pulse mr-2"></i> SERVICIOS
            </a>
            <a href="{{ route('client.mascotas.index') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium hover:bg-bluey-light hover:text-bluey-dark">
                <i class="bi bi-clipboard-heart mr-2"></i> MASCOTAS
            </a>
            <a href="{{ route('client.citas.index') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium hover:bg-bluey-light hover:text-bluey-dark">
                <i class="bi bi-calendar-check mr-2"></i> CITAS
            </a>

            {{-- Ícono de Campana de Notificaciones para el menú móvil --}}
            @auth
            <a href="{{ route('notifications.index') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium hover:bg-bluey-light hover:text-bluey-dark flex items-center justify-between">
                <span class="flex items-center">
                    <i class="bi bi-bell text-lg mr-2"></i> NOTIFICACIONES
                </span>
                @if (Auth::user()->unreadNotifications->count() > 0)
                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                    {{ Auth::user()->unreadNotifications->count() }}
                </span>
                @endif
            </a>
            <hr class="my-2 border-gray-200">
            <a href="{{ route('profile.edit') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium hover:bg-bluey-light hover:text-bluey-dark flex items-center justify-between">
                <span class="flex items-center">
                    <i class="bi bi-person-fill text-lg mr-2"></i> MI PERFIL
                </span>
                @if (Auth::user()->needsProfileCompletion)
                <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-ping-once-small"></span>
                @endif
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="menu-link block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-100 hover:text-red-800">
                    <i class="bi bi-box-arrow-right mr-2"></i> CERRAR SESIÓN
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium hover:bg-bluey-light hover:text-bluey-dark">
                <i class="bi bi-box-arrow-in-right mr-2"></i> INICIAR SESIÓN
            </a>
            @endauth
        </div>
    </div>
</nav>