<nav class="navbar fixed top-0 left-0 w-full bg-white shadow-md z-40">
    <div class="navbar-container flex justify-between items-center h-16 px-4 md:px-6 lg:px-8">

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="logo flex items-center flex-shrink-0 space-x-2">
            <img src="{{ asset('img/logo-blueyvet.png') }}" alt="BlueyVet" class="logo-img h-10 w-auto">
            <span class="logo-text text-bluey-dark text-2xl font-bold hidden sm:inline">BLUEYVET</span>
        </a>

        {{-- Botón de Hamburguesa para el menú principal (solo en móviles) --}}
        <div class="md:hidden flex-grow flex justify-end">
            <button id="mobile-menu-button" class="text-bluey-dark hover:text-bluey-primary focus:outline-none focus:ring-2 focus:ring-bluey-primary rounded-md p-2">
                <i class="bi bi-list text-3xl"></i>
            </button>
        </div>

        {{-- Menú de Navegación (oculto en móviles, visible en md y arriba) --}}
        <div id="main-menu" class="hidden md:flex flex-grow justify-center items-center space-x-6 lg:space-x-8">
            <div class="dropdown relative group"> {{-- ¡Aquí la clave! 'group' para hover --}}
                <button class="menu-link dropdown-toggle flex items-center"> {{-- Eliminado id="desktop-product-dropdown-toggle" --}}
                    PRODUCTOS <i class="bi bi-chevron-down dropdown-icon ml-1 text-sm"></i>
                </button>
                {{-- El dropdown-menu se controla con 'group-hover:block' --}}
                <div class="dropdown-menu absolute hidden group-hover:block bg-white shadow-lg rounded-md py-1 mt-0 w-40 z-48"> {{-- Eliminado id="desktop-product-dropdown-menu" y clases de animación --}}
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

             <a href="{{ route('faqs.index') }}" class="menu-link">
                <i class="bi bi-clipboard-heart mr-1"></i> FAQ
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





        {{-- Menú de Acción (Carrito y Perfil) - Sin cambios aquí --}}
        <div class="action-icons flex items-center space-x-3 ml-auto md:ml-6">
            {{-- Ícono de Carrito --}}
            <!-- <div id="carrito-icono" class="icon-btn cart-icon relative" title="Carrito">
                <i class="bi bi-cart text-xl md:text-2xl text-bluey-dark hover:text-bluey-primary"></i>
                <span class="cart-badge absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center">{{ count(session('cart', [])) }}</span>
            </div> -->
            <!-- 
                     <x-cart-floating /> -->


                     <!-- cambio aqui  Añadi esto nada mas-->
            <div id="carrito-icono" class="icon-btn cart-icon" title="Carrito" onclick="toggleCart()">
                <i class="bi bi-cart"></i>
                <span id="cart-count" class="cart-badge">{{ count(session('cart', [])) }}</span>
            </div>

            <x-cart-floating :cart="$cart" :total="$total" />
<!-- fin del cambio  -->



            {{-- Menú de Usuario/Perfil (sin cambios) --}}
            <div class="relative group">
                <button class="flex items-center space-x-1 text-bluey-dark hover:text-bluey-primary focus:outline-none px-1 py-1 rounded-md transition-colors duration-200" id="user-profile-menu-button" aria-expanded="true" aria-haspopup="true">
                    @auth
                    <span class="text-sm font-semibold hidden lg:inline">{{ __('Hola,') }} {{ Str::limit(Auth::user()->name, 10, '...') }}</span>
                    <span class="text-bluey-dark font-bold text-lg p-2 rounded-full bg-bluey-light hidden md:inline lg:hidden">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    @endauth
                    <i class="bi bi-person text-xl md:text-2xl"></i>
                    <i class="bi bi-chevron-down text-xs ml-1 transition-transform duration-200 ease-in-out"></i>
                </button>
                <div id="user-profile-dropdown-menu" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 invisible scale-95 transform transition-all duration-200 ease-in-out origin-top-right z-30">
                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="user-profile-menu-button">
                        @auth
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark" role="menuitem">
                            <i class="bi bi-person-fill mr-2"></i> {{ __('Mi Perfil') }}
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

    {{-- Menú móvil desplegable (inicialmente oculto) --}}
    <div id="mobile-menu" class="md:hidden hidden absolute top-16 left-0 w-full bg-white shadow-lg py-2 z-20">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <div class="dropdown relative px-2">
                <button class="menu-link dropdown-toggle w-full text-left flex items-center justify-between py-2 px-3 rounded-md hover:bg-bluey-light hover:text-bluey-dark" id="mobile-product-dropdown-toggle">
                    PRODUCTOS <i class="bi bi-chevron-down dropdown-icon ml-1 text-sm"></i>
                </button>

                <!-- cambio aqui -->
                <div id="mobile-product-dropdown-menu" class="dropdown-menu hidden pl-4 pt-1 pb-2"> {{-- Mantener 'hidden' para control JS --}}
                    <a href="{{ route('productos.por_categoria', ['id' => 2]) }}" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark">
                        <i class="bi bi-capsule mr-2"></i> Farmacia
                    </a>
                    <a href="{{ route('productos.por_categoria', ['id' => 1]) }}" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-bluey-light hover:text-bluey-dark">
                        <i class="bi bi-bag-heart mr-2"></i> Petshop
                    </a>
                </div>
            </div>

            <a href="{{ url('/') }}" class="menu-link block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'active' : '' }} hover:bg-bluey-light hover:text-bluey-dark">
                <i class="bi bi-house mr-2"></i> INICIO
            </a>
            <a href="#" class="menu-link">
                <i class="bi bi-newspaper mr-2"></i> BLOG
            </a>
            <a href="{{ route('client.servicios.index') }}" class="menu-link">
                <i class="bi bi-heart-pulse mr-2"></i> SERVICIOS
            </a>
            <a href="{{ route('client.mascotas.index') }}" class="menu-link">
                <i class="bi bi-clipboard-heart mr-2"></i> MASCOTAS
            </a>
            <a href="{{ route('client.citas.index') }}" class="menu-link">
                <i class="bi bi-calendar-check mr-2"></i> CITAS
            </a>
        </div>
    </div>
</nav>