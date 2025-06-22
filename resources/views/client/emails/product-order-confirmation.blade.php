<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Tu Compra en BlueyVet</title>
    </head>
<body class="font-sans leading-relaxed text-bluey-dark bg-bluey-secondary-light2 m-0 p-0">

    <div class="max-w-3xl mx-auto my-5 bg-white rounded-lg shadow-lg border border-bluey-light overflow-hidden">

        <div class="bg-bluey-primary text-white p-8 text-center">
            <h1 class="text-3xl font-bold m-0">¡Tu Pedido ha sido Confirmado!</h1>
            <p class="text-lg mt-2">Gracias por confiar en BlueyVet.</p>
        </div>

        <div class="p-8">
            <p class="mb-4 text-lg">Hola **{{ $order->user->name ?? 'Cliente Valioso' }}**,</p>

            <p class="mb-6">¡Tu compra en BlueyVet ha sido confirmada con éxito! Nos alegra mucho que hayas elegido nuestros productos para tu mascota.</p>

            <div class="bg-bluey-secondary-light2 p-6 rounded-md mb-6 border border-bluey-light">
                <h2 class="text-xl font-semibold text-bluey-dark mb-4 pb-2 border-b border-bluey-light">Resumen de tu Pedido #{{ $order->id }}</h2>
                <div class="mb-2">
                    <strong class="inline-block w-1/3 text-bluey-secondary">Fecha del Pedido:</strong>
                    <span class="inline-block w-2/3">{{ \Carbon\Carbon::parse($order->created_at)->isoFormat('dddd D [de] MMMM [del]YYYY, HH:mm') }}</span>
                </div>
                <div class="mb-2">
                    <strong class="inline-block w-1/3 text-bluey-secondary">Monto Total:</strong>
                    <span class="inline-block w-2/3 text-lg font-semibold">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</span>
                </div>
                <div class="mb-2">
                    <strong class="inline-block w-1/3 text-bluey-secondary">Estado del Pedido:</strong>
                    <span class="inline-block w-2/3">{{ ucfirst($order->status) }}</span>
                </div>
                @if($order->paypal_order_id)
                <div class="mb-2">
                    <strong class="inline-block w-1/3 text-bluey-secondary">ID de Transacción PayPal:</strong>
                    <span class="inline-block w-2/3">{{ $order->paypal_order_id }}</span>
                </div>
                @endif
                <div class="mt-4 p-3 bg-bluey-light-yellow rounded-md text-bluey-dark text-sm border border-bluey-gold-yellow">
                    <p class="m-0 font-semibold">¡Importante!</p>
                    <p class="m-0">Se ha adjuntado a este correo tu comprobante de compra en formato PDF para tu referencia.</p>
                </div>
            </div>

            <div class="bg-bluey-secondary-light2 p-6 rounded-md mb-6 border border-bluey-light">
                <h2 class="text-xl font-semibold text-bluey-dark mb-4 pb-2 border-b border-bluey-light">Artículos de tu Pedido</h2>
                <table class="w-full text-left table-auto border-collapse">
                    <thead>
                        <tr class="bg-bluey-light text-bluey-dark">
                            <th class="p-3 font-semibold text-sm uppercase">Producto</th>
                            <th class="p-3 font-semibold text-sm uppercase text-center">Cantidad</th>
                            <th class="p-3 font-semibold text-sm uppercase text-right">Precio Unitario</th>
                            <th class="p-3 font-semibold text-sm uppercase text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                        <tr class="border-b border-bluey-light last:border-b-0"> {{-- last:border-b-0 para la última fila --}}
                            <td class="p-3 text-bluey-dark">{{ $item->name }}</td>
                            <td class="p-3 text-center text-bluey-dark">{{ $item->quantity }}</td>
                            <td class="p-3 text-right text-bluey-dark">{{ number_format($item->price, 2) }} {{ $order->currency }}</td>
                            <td class="p-3 text-right text-bluey-dark">{{ number_format($item->quantity * $item->price, 2) }} {{ $order->currency }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="p-3 text-right text-lg font-bold text-bluey-dark border-t border-bluey-light">Total:</td>
                            <td class="p-3 text-right text-lg font-bold text-bluey-dark border-t border-bluey-light">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-bluey-secondary-light2 p-6 rounded-md mb-6 border border-bluey-light">
                <h2 class="text-xl font-semibold text-bluey-dark mb-4 pb-2 border-b border-bluey-light">Dirección de Envío</h2>
                <p class="m-0 text-bluey-dark">{{ $order->customer_address ?? 'Dirección no especificada. Por favor, contáctanos.' }}</p>
                <p class="m-0 text-bluey-dark text-sm mt-2">Te notificaremos una vez que tu pedido sea enviado con los detalles de seguimiento.</p>
            </div>

            <p class="mb-8">Si tienes alguna pregunta sobre tu pedido o necesitas realizar algún cambio, no dudes en contactar a nuestro equipo de soporte.</p>

            <div class="text-center mb-6">
                <a href="{{ url('/client/orders/' . $order->id) }}" class="inline-block bg-bluey-primary text-white px-8 py-3 rounded-md font-bold text-lg no-underline hover:opacity-90 transition duration-300">
                    Ver Detalles de mi Pedido
                </a>
                @if (isset($trackingLink) && $trackingLink)
                <span class="inline-block mx-3"></span> {{-- Espaciador --}}
                <a href="{{ $trackingLink }}" class="inline-block bg-bluey-gold-yellow text-bluey-dark px-8 py-3 rounded-md font-bold text-lg no-underline hover:opacity-90 transition duration-300">
                    Seguir mi Envío
                </a>
                @endif
            </div>
        </div>

        <div class="bg-bluey-dark text-white p-5 text-center text-sm">
            <p class="m-0 mb-1">Este es un correo electrónico automatizado, por favor no respondas a esta dirección.</p>
            <p class="m-0">&copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.</p>
            <p class="m-0 mt-2">
                <a href="{{ url('/privacy') }}" class="text-bluey-light underline hover:text-bluey-light-yellow transition duration-300">Política de Privacidad</a> |
                <a href="{{ url('/terms') }}" class="text-bluey-light underline hover:text-bluey-light-yellow transition duration-300">Términos de Servicio</a>
            </p>
        </div>

    </div>

</body>
</html>