{{-- resources/views/client/checkout.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Confirmación de Pago</h1>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">¡Éxito!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">¡Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Detalles del Servicio:</h2>
        <p class="text-gray-600 mb-2">
            <span class="font-semibold">Servicio:</span> {{ $service->name ?? 'N/A' }}
        </p>
        <p class="text-gray-600 mb-2">
            <span class="font-semibold">Descripción:</span> {{ $service->description ?? 'N/A' }}
        </p>
        <p class="text-gray-600 mb-2">
            <span class="font-semibold">Precio:</span> S/{{ number_format($order->amount ?? 0, 2) }} {{ $order->currency ?? 'N/A' }}
        </p>
        <p class="text-gray-600 mb-2">
            <span class="font-semibold">ID de Orden Local:</span> {{ $order->id ?? 'N/A' }}
        </p>

        <h3 class="text-xl font-semibold text-gray-700 mt-6 mb-4">Procesar Pago con PayPal:</h3>
        {{-- Contenedor donde se renderizarán los botones de PayPal --}}
        <div id="paypal-button-container" class="mt-4"></div>
        <p class="text-sm text-gray-500 mt-2">Serás redirigido a PayPal para completar tu compraa.</p>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('client.servicios.index') }}" class="text-primary hover:underline">Volver a Servicios</a>
    </div>

</div>

@push('scripts')
    {{-- PayPal JavaScript SDK --}}
    {{-- Asegúrate de que el client-id y la currency se carguen correctamente desde config --}}
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.sandbox.client_id') }}&currency={{ config('services.paypal.currency', 'USD') }}&intent=capture"></script>

    {{-- Estos scripts de tipo "application/json" son para pasar datos de PHP a JavaScript de forma segura --}}
    {{-- La doble exclamación {!! !!} es para renderizar HTML sin escapar, útil con json_encode --}}
    <script id="service-data" type="application/json">
        {!! json_encode($service) !!}
    </script>
    <script id="order-data" type="application/json">
        {!! json_encode($order) !!}
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Almacena los datos del servicio y la orden en variables JavaScript
            const serviceData = JSON.parse(document.getElementById('service-data').textContent);
            const orderData = JSON.parse(document.getElementById('order-data').textContent);

            // Define orderDetails para usarlo de forma consistente en el resto del script
            const orderDetails = {
                order_id: orderData.id,
                service_id: serviceData.id,
                amount: orderData.amount, // Usa el monto de la ServiceOrder ($order->amount)
                service_name: serviceData.name
            };

            // ****** DEBUGGING Adicional: LOGGEA orderDetails ANTES DE ENVIARLO ******
            console.log('Final orderDetails antes de PayPal:', orderDetails);
            console.log('JSON.stringify(orderDetails) antes de enviar:', JSON.stringify(orderDetails));
            // **********************************************************************


            paypal.Buttons({
                // Configura la creación de la orden
                createOrder: function(data, actions) {
                    return fetch("{{ route('payments.checkout') }}", { // Usamos route('payments.checkout') para la ruta POST que crea la orden de PayPal
                            method: "POST", // DEBE SER POST
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}" // Laravel CSRF token
                            },
                            body: JSON.stringify({
                                // Envía los datos relevantes al backend
                                service_id: orderDetails.service_id,
                                amount: orderDetails.amount, // **USAR orderDetails.amount**
                                service_name: orderDetails.service_name,
                                order_id: orderDetails.order_id // Este es tu ID de ServiceOrder local
                            })
                        })
                        .then(response => {
                            console.log('Respuesta cruda del fetch (createOrder):', response);
                            if (!response.ok) {
                                console.error('El servidor respondió con un error de estado:', response.status);
                                return response.text().then(text => {
                                    console.error('Detalles del error del servidor:', text);
                                    try {
                                        const errorJson = JSON.parse(text);
                                        alert('Hubo un error al crear la orden de PayPal: ' + (errorJson.error || 'Error desconocido') + '. Revisa la consola para más detalles.');
                                    } catch (e) {
                                        alert('Hubo un error al crear la orden de PayPal. Detalles: ' + text + '. Revisa la consola.');
                                    }
                                    throw new Error('Error en el servidor: ' + text);
                                });
                            }
                            return response.json();
                        })
                        .then(orderDataPaypal => {
                            console.log('Datos de la orden de PayPal recibidos:', orderDataPaypal);

                            if (orderDataPaypal.id) {
                                return orderDataPaypal.id; // Retorna el ID de la orden de PayPal
                            } else {
                                console.error('Error al crear la orden de PayPal: No se recibió ID de PayPal.', orderDataPaypal);
                                alert('Error al crear la orden de PayPal. Por favor, intenta de nuevo. Revisa la consola para más detalles.');
                                // Considera redirigir a una página de cancelación o error aquí
                                window.location.href = "{{ route('payments.cancel', ['order_id_local' => $order->id]) }}";
                            }
                        })
                        .catch(error => {
                            console.error('Error en la llamada createOrder o en el procesamiento de la respuesta:', error);
                            alert('Error de red o procesamiento al crear la orden de PayPal. Por favor, intenta de nuevo. Revisa la consola para más detalles.');
                            // Considera redirigir a una página de cancelación o error aquí
                            window.location.href = "{{ route('payments.cancel', ['order_id_local' => $order->id]) }}";
                        });
                },

                // Configura la captura de la orden (cuando el usuario aprueba el pago)
                onApprove: function(data, actions) {
                    return actions.order.capture().then(function(details) {
                        if (details.status === 'COMPLETED') {
                            console.log('Captura de PayPal exitosa:', details);
                            // Redirige a tu ruta de éxito con los detalles de la transacción de PayPal
                            // Asegúrate de que el PayerID se pase correctamente (puede estar en data o details.payer)
                            window.location.href = "{{ route('payments.success') }}?token=" + data.orderID + "&PayerID=" + (details.payer ? details.payer.payer_id : data.payerID);
                        } else {
                            console.warn('Captura de PayPal no completada, estado:', details.status, details);
                            // Redirige a una página de cancelación o error
                            window.location.href = "{{ route('payments.cancel', ['order_id_local' => $order->id]) }}";
                        }
                    }).catch(function(error) {
                        console.error('Error en la llamada onApprove (captura):', error);
                        alert('Ha ocurrido un error con el pago de PayPal. Por favor, intenta de nuevo.');
                        window.location.href = "{{ route('payments.cancel', ['order_id_local' => $order->id]) }}";
                    });
                },

                // Manejo de cancelación del pago
                onCancel: function(data) {
                    console.log('Pago cancelado:', data);
                    alert('¡Pago cancelado!');
                    window.location.href = "{{ route('payments.cancel', ['order_id_local' => $order->id]) }}";
                },

                // Manejo de errores
                onError: function(err) {
                    console.error('Error de PayPal:', err);
                    alert('Ha ocurrido un error con el pago de PayPal. Por favor, intenta de nuevo.');
                    window.location.href = "{{ route('payments.cancel', ['order_id_local' => $order->id]) }}";
                }

            }).render('#paypal-button-container'); // Renderiza los botones en el div con ese ID
        });
    </script>
@endpush