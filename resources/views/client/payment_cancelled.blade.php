{{-- resources/views/client/payment_cancelled.blade.php --}}

@extends('layouts.app') {{-- O el layout que uses para tus páginas --}}

@section('content')
<div class="container mx-auto p-6 text-center">
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <h1 class="text-2xl font-bold mb-2">¡Pago Cancelado o Fallido!</h1>
        <p>La transacción de PayPal ha sido cancelada o no pudo ser completada. Por favor, intenta de nuevo o contacta con soporte si el problema persiste.</p>

        {{-- Aquí se mostrarán los mensajes flasheados del controlador --}}
        @if(Session::has('error'))
            <p class="mt-2 text-sm text-red-800">{{ Session::get('error') }}</p>
        @endif
        @if(Session::has('info'))
            <p class="mt-2 text-sm text-blue-800">{{ Session::get('info') }}</p>
        @endif
        @if(Session::has('warning'))
            <p class="mt-2 text-sm text-yellow-800">{{ Session::get('warning') }}</p>
        @endif
    </div>

    <div class="mt-6">
        <a href="{{ route('client.servicios.index') }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Volver a la lista de Servicios
        </a>
        {{-- O un enlace para volver a intentar el pago del mismo servicio --}}
        {{-- @if(isset($order) && $order->service)
            <a href="{{ route('payments.show_checkout_page', ['service' => $order->service->id]) }}" class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-4">
                Intentar Pagar de Nuevo
            </a>
        @endif --}}
    </div>
</div>
@endsection