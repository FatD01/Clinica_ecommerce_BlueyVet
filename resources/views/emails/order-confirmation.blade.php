<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra - BlueyVet</title>
    <style>
        /* Copia y pega el CSS de tu contact.blade.php aquí */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #1e3a8a; text-align: center; margin-bottom: 20px; }
        p { margin-bottom: 10px; line-height: 1.5; }
        .highlight { background-color: #e0f2fe; padding: 15px; border-left: 5px solid #3b82f6; margin-top: 20px; }
        .button { display: inline-block; background-color: #3b82f6; color: #ffffff !important; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 0.8em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Confirmación de Compra</h1>

        <p>¡Hola {{ $order->user->name ?? 'Cliente' }}!</p>

        <p>Gracias por tu compra en BlueyVet. Tu orden #<strong>{{ $order->id }}</strong> ha sido confirmada y procesada.</p>

        <div class="highlight">
            <p><strong>Servicio:</strong> {{ $order->service->name ?? 'N/A' }}</p>
            <p><strong>Descripción:</strong> {{ $order->service->description ?? 'N/A' }}</p>
            <p><strong>Monto Total:</strong> {{ $order->amount }} {{ $order->currency }}</p>
            <p><strong>Estado de la Orden:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Fecha de Compra:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            @if($order->paypal_order_id)
                <p><strong>ID de Transacción de PayPal:</strong> {{ $order->paypal_order_id }}</p>
            @endif
        </div>

        <p>Puedes ver los detalles de tu orden iniciando sesión en tu cuenta de BlueyVet.</p>

        <p style="text-align: center;">
            <a href="{{ url('/profile/orders') }}" class="button">Ver Mis Órdenes</a>
        </p>

        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a esta dirección.</p>
            <p>&copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>