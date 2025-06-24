@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Encabezado con efecto de neón sutil -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">
                <span class="bg-clip-text text-bluey-dark">
                    Mis Pedidos
                </span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Todos tus pedidos en un solo lugar
            </p>
        </div>

        @if($orders->isEmpty())
        <!-- Estado vacío con ilustración -->
        <div class="text-center py-16">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No hay pedidos aún</h3>
            <p class="mt-1 text-gray-500">Cuando realices tu primer pedido, aparecerá aquí.</p>
            <div class="mt-6">
                <a href="{{ route('client.products.petshop') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Ir a comprar
                </a>
            </div>
        </div>
        @else
        <div class="space-y-6">
            @foreach($orders as $order)
            <!-- Tarjeta de pedido mejorada -->
            <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
                <!-- Encabezado interactivo -->
                <div class="p-5 sm:p-6 cursor-pointer" @click="open = !open">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 bg-indigo-100 p-3 rounded-lg">
                                <svg class="h-6 w-6 stroke-bluey-dark" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Pedido #{{ $order->id }}</h3>
                                <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:space-x-6">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500 mt-1 sm:mt-0">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        {{ $order->items->sum('quantity') }} productos
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 sm:mt-0 flex items-center justify-between sm:block">
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-500">Total</p>
                                <p class="text-2xl font-bold text-bluey-primary">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span x-text="open ? 'Ocultar detalles' : 'Ver detalles'"></span>
                            <svg x-show="!open" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            <svg x-show="open" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Detalles del pedido (acordeón) -->
                <div x-show="open" x-collapse class="border-t border-gray-200">
                    <div class="px-5 sm:px-6 py-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="mr-2 h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Detalles del pedido
                        </h4>

                        <!-- Sección de información de pago (existente) -->
                        <div class="mb-6">
                            <h5 class="text-md font-medium text-gray-900 mb-3 flex items-center">
                                <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Información de pago
                            </h5>

                            @if($order->payment_details)
                            @php $paymentDetails = $order->payment_details; @endphp
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Estado del pago</p>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                @if($order->status === 'COMPLETED') bg-green-100 text-green-800
                                                @elseif($order->status === 'PENDING') bg-yellow-100 text-yellow-800
                                                @elseif($order->status === 'FAILED') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $order->status }}
                                            </span>
                                        </p>
                                    </div>

                                    @if($order->paypal_payment_id)
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">ID de transacción</p>
                                        <p class="mt-1 text-sm font-mono text-gray-900">{{ $order->paypal_payment_id }}</p>
                                    </div>
                                    @endif

                                    @if(isset($paymentDetails['payer']['email_address']))
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Email de PayPal</p>
                                        <p class="mt-1 text-sm text-gray-900">{{ $paymentDetails['payer']['email_address'] }}</p>
                                    </div>
                                    @endif

                                    {{-- Aquí se muestra la dirección de envío desde payment_details (si existe y es de PayPal) --}}
                                    @if(isset($paymentDetails['payer']['address']))
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-medium text-gray-500">Dirección de envío (PayPal)</p>
                                        <div class="mt-1 text-sm text-gray-900">
                                            @if(isset($paymentDetails['payer']['address']['address_line_1']))
                                            <p>{{ $paymentDetails['payer']['address']['address_line_1'] }}</p>
                                            @endif
                                            @if(isset($paymentDetails['payer']['address']['address_line_2']))
                                            <p>{{ $paymentDetails['payer']['address']['address_line_2'] }}</p>
                                            @endif
                                            @if(isset($paymentDetails['payer']['address']['admin_area_2']))
                                            <p>{{ $paymentDetails['payer']['address']['admin_area_2'] }}, {{ $paymentDetails['payer']['address']['admin_area_1'] }} {{ $paymentDetails['payer']['address']['postal_code'] }}</p>
                                            @endif
                                            @if(isset($paymentDetails['payer']['address']['country_code']))
                                            <p>{{ $paymentDetails['payer']['address']['country_code'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-500">No hay detalles de pago adicionales disponibles.</p>
                            </div>
                            @endif
                        </div>

                        <!-- NUEVA SECCIÓN: Dirección de Envío (desde customer_address) -->
                        <div class="mb-6">
                            <h5 class="text-md font-medium text-gray-900 mb-3 flex items-center">
                                <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Dirección de Envío
                            </h5>

                            @if($order->customer_address)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="mt-1 text-sm text-gray-900">{{ $order->customer_address }}</p>
                                {{-- Si customer_address es una cadena JSON, puedes parsearla y mostrarla aquí.
                                   Ejemplo: @php $parsedAddress = json_decode($order->customer_address, true); @endphp
                                   <p>{{ $parsedAddress['street'] ?? '' }}</p>
                                   <p>{{ $parsedAddress['city'] ?? '' }}</p>
                                   ...
                                   Por ahora, se muestra como una sola cadena, siguiendo la lógica del PDF.
                                --}}
                            </div>
                            
                            @else
                            <div class="bg-bluey-light-yellow border-l-4 border-bluey-gold-yellow p-4 text-center">
                                <p class="text-sm text-bluey-dark flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-bluey-secondary mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    No hay dirección de envío registrada para esta orden.
                                </p>
                                <div class="mt-3">
                                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-bluey-primary hover:bg-bluey-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bluey-primary transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                        Completa tu perfil
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                        <!-- FIN NUEVA SECCIÓN: Dirección de Envío -->


                        <!-- Sección de productos (existente) -->
                        <div>
                            <h5 class="text-md font-medium text-gray-900 mb-3 flex items-center">
                                <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Productos comprados
                            </h5>

                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Producto</th>
                                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Precio unitario</th>
                                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Cantidad</th>
                                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($order->items as $item)
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                <div class="flex items-center">
                                                    @if($item->product && $item->product->image)
                                                    <div class="h-10 w-10 flex-shrink-0">
                                                        <img class="h-10 w-10 rounded-md object-cover" src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->name }}">
                                                    </div>
                                                    @else
                                                    <div class="h-10 w-10 flex-shrink-0 bg-gray-200 rounded-md flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                    @endif
                                                    <div class="ml-4">
                                                        <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-gray-500">
                                                {{ number_format($item->price, 2) }} {{ $order->currency }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-gray-500">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-medium text-gray-900">
                                                {{ number_format($item->quantity * $item->price, 2) }} {{ $order->currency }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="hidden sm:table-cell"></td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-bold text-gray-900 border-t border-gray-200">
                                                Total: {{ number_format($order->total_amount, 2) }} {{ $order->currency }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection