@extends('layouts.app') {{-- Asegúrate de que este layout incluye Tailwind CSS --}}

@section('content')
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    ¡Pago Exitoso!
                </h2>
            </div>
            <div class="mt-8 space-y-6">
                @if(Session::has('success'))
                    <div class="bg-blue-100 border border-blue-400 text-blue-300 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">¡Éxito!</strong>
                        <span class="block sm:inline">{{ Session::get('success') }}</span>
                    </div>
                @else
                    <div class="bg-blue-100 border border-blue-400 text-blue-500 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">¡Éxito!</strong>
                        <span class="block sm:inline">Tu pago ha sido procesado correctamente. ¡Gracias por tu compra!</span>
                    </div>
                @endif

                <p class="text-gray-700 text-center">
                    Hemos recibido tu pago y tu orden ha sido confirmada. En breve recibirás un correo electrónico con los detalles de tu compra.
                </p>

                <div class="text-center">
                    <a href="{{ route('client.home') }}" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Volver al inicio
                    </a>
                    {{-- Si tienes una ruta para ver las órdenes del usuario: --}}
                    {{-- <a href="{{ route('client.orders.index') }}" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mt-2">Ver mis órdenes</a> --}}
                </div>
            </div>
        </div>
    </div>
@endsection