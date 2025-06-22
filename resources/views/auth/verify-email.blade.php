<x-guest-layout>
    {{-- Reemplaza 'mb-4 text-sm text-gray-600' --}}
    <div class="mb-6 text-base text-bluey-dark leading-relaxed">
        {{ __('¡Gracias por registrarte! Antes de empezar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar? Si no recibiste el correo, te enviaremos otro con gusto.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        {{-- Reemplaza 'mb-4 font-medium text-sm text-green-600' --}}
        <div class="mb-6 font-semibold text-sm text-green-700 bg-green-50 p-3 rounded-md border border-green-200">
            {{ __('Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.') }}
        </div>
    @endif

    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0 sm:space-x-4">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <div>
                {{-- Usando tu clase de botón principal, si la tienes definida --}}
                <x-primary-button class="w-full justify-center bg-bluey-primary hover:bg-bluey-dark focus:ring-bluey-primary">
                    {{ __('Reenviar correo de verificación') }}
                </x-primary-button>
                {{-- O si usas el botón por defecto de Breeze y quieres personalizarlo: --}}
                {{--
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-bluey-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bluey-dark focus:bg-bluey-dark active:bg-bluey-dark focus:outline-none focus:ring-2 focus:ring-bluey-primary focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center">
                    {{ __('Reenviar correo de verificación') }}
                </button>
                --}}
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="underline text-sm text-bluey-dark hover:text-bluey-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bluey-primary w-full sm:w-auto text-center px-4 py-2">
                {{ __('Cerrar Sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>