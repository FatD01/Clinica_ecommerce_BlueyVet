<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-bluey-light py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-xl border border-bluey-light">
            <div class="text-center">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('img/logo-blueyvet.png') }}" alt="BlueyVet Logo" class="mx-auto h-20 w-auto">
                </a>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-bluey-dark">
                    {{ __('Regístrate en BlueyVet') }}
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    {{ __('Crea tu cuenta para acceder a todos nuestros servicios.') }}
                </p>
            </div>

            <x-auth-session-status class="mb-4 text-center text-sm font-medium text-bluey-primary" :status="session('status')" />

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Nombre Completo')" class="block text-sm font-medium text-bluey-dark" />
                    <x-text-input
                        id="name"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-bluey-primary focus:border-bluey-primary sm:text-sm"
                        type="text"
                        name="name"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Ej: Juan Pérez"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-600" />
                </div>

                <div class="mt-4">
                    <x-input-label for="email" :value="__('Correo Electrónico')" class="block text-sm font-medium text-bluey-dark" />
                    <x-text-input
                        id="email"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-bluey-primary focus:border-bluey-primary sm:text-sm"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autocomplete="username"
                        placeholder="tu_email@ejemplo.com"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" :value="__('Contraseña')" class="block text-sm font-medium text-bluey-dark" />
                    <x-text-input
                        id="password"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-bluey-primary focus:border-bluey-primary sm:text-sm"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="block text-sm font-medium text-bluey-dark" />
                    <x-text-input
                        id="password_confirmation"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-bluey-primary focus:border-bluey-primary sm:text-sm"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-600" />
                </div>

                <div>
                    <x-primary-button class="w-full justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-bluey-primary hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bluey-primary transition-colors duration-200">
                        {{ __('Registrarme') }}
                    </x-primary-button>
                </div>
            </form>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">{{ __('O') }}</span>
                </div>
            </div>

            <div class="flex items-center justify-center">
                <a href="{{ route('auth.google.redirect') }}" class="inline-flex items-center w-full justify-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-bluey-primary focus:ring-offset-2 transition ease-in-out duration-150">
                    <img src="https://img.icons8.com/color/24/000000/google-logo.png" alt="Google logo" class="w-5 h-5 mr-3">
                    {{ __('Registrarse con Google') }}
                </a>
            </div>

            <div class="text-center text-sm mt-6">
                {{ __('¿Ya tienes una cuenta?') }}
                <a class="font-medium text-bluey-primary hover:text-bluey-dark ml-1" href="{{ route('login') }}">
                    {{ __('Inicia sesión aquí') }}
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>