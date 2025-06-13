{{-- resources/views/client/success.blade.php --}}

@extends('layouts.app') {{-- Asegúrate de que este layout incluye Tailwind CSS --}}

@section('content')
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg">
            <div class="text-center">
                {{-- Puedes añadir una imagen o icono de éxito aquí, como un checkmark --}}
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    ¡Pago Exitoso!
                </h2>
            </div>
            <div class="mt-8 space-y-6">
                @if(Session::has('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">¡Éxito!</strong>
                        <span class="block sm:inline">{{ Session::get('success') }}</span>
                    </div>
                @else
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">¡Éxito!</strong>
                        <span class="block sm:inline">Tu pago ha sido procesado correctamente. ¡Gracias por tu compra!</span>
                    </div>
                @endif

                <p class="text-gray-700 text-center">
                    Hemos recibido tu pago y tu orden ha sido confirmada. En breve recibirás un correo electrónico con los detalles de tu compra.
                    Ahora puedes programar tu cita para el servicio adquirido.
                </p>

                {{-- Opcional: Mostrar detalles de la transacción si se pasan del controlador --}}
                @if(isset($order) && $order)
                    <div class="text-gray-600 text-sm mt-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                        <p class="font-bold mb-2">Detalles de tu compra:</p>
                        <p><span class="font-semibold">Servicio:</span> {{ $order->service->name ?? 'N/A' }}</p>
                        <p><span class="font-semibold">Monto Pagado:</span> S/{{ number_format($order->amount ?? 0, 2) }} {{ $order->currency ?? 'N/A' }}</p>
                        <p><span class="font-semibold">ID de Orden Local:</span> {{ $order->id ?? 'N/A' }}</p>
                        @if(isset($paypal_order_id))
                            <p><span class="font-semibold">ID de Transacción PayPal:</span> {{ $paypal_order_id }}</p>
                        @endif
                    </div>
                @endif


                <div class="text-center mt-6">
                    {{-- Botón "Programar mi cita" con colores de Bluey (ej. naranja/azul) --}}
                    {{-- Asegúrate de que client.citas.index sea la ruta correcta a tu página de citas --}}
                    <a href="{{ route('client.citas.index') }}"
                       class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-lg font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 ease-in-out">
                        <i class="bi bi-calendar-check mr-2"></i> Programar mi cita
                    </a>

                    {{-- Botón "Volver al inicio" --}}
                    <a href="{{ route('client.home') }}"
                       class="group relative w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mt-3 transition-all duration-200 ease-in-out">
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection