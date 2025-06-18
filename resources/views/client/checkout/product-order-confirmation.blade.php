<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de tu Compra en BlueyVet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #393859;
            text-align: center;
        }

        p {
            margin-bottom: 10px;
        }

        .order-details {
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            margin-bottom: 20px;
        }

        .order-details h2 {
            color: #555;
            font-size: 1.2em;
            margin-top: 0;
        }

        .item-list {
            list-style: none;
            padding: 0;
        }

        .item-list li {
            padding: 8px 0;
            border-bottom: 1px dotted #eee;
            display: flex;
            justify-content: space-between;
        }

        .item-list li:last-child {
            border-bottom: none;
        }

        .total-section {
            text-align: right;
            margin-top: 20px;
            font-size: 1.1em;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9em;
            color: #777;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff !important;
            /* !important para sobrescribir estilos de email */
            padding: 10px 20px;
            margin-top: 20px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>¡Gracias por tu compra en BlueyVet!</h1>

        <p>Hola {{ $order->user->name ?? 'Cliente Valorado' }},</p>
        <p>Tu pedido de productos ha sido confirmado y procesado con éxito. A continuación, los detalles de tu compra:</p>

        <div class="order-details">
            <h2>Detalles del Pedido #{{ $order->id }}</h2>
            <p><strong>Fecha del Pedido:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>ID de Pago (PayPal):</strong> {{ $order->paypal_payment_id ?? 'N/A' }}</p>
        </div>

        <h2>Productos Comprados:</h2>
        <ul class="item-list">
            @foreach($order->items as $item)
            <li>
                <span> Cant: {{ $item->quantity }} | Prod: {{ $item->name }} </span>
                <span>{{ number_format($item->price, 2) }} {{ $order->currency }} c/u</span>
            </li>
            @endforeach
        </ul>

        <div class="total-section">
            <p><strong>Total Pagado:</strong> {{ number_format($order->total_amount, 2) }} {{ $order->currency }}</p>
        </div>

        <!-- <p style="margin-top: 15px; text-align: center;">
             Para mas seguridad, el equipo de Blueyvet ha enviado un correo con la misma información detallada de tu compra.
            <a href="{{ route('cart_payments.order_details', $order->id) }}"
                style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px;">
                Ver Detalles de la Compra
            </a>
        </p> -->

        <div class="footer">
            <p>Si tienes alguna pregunta, no dudes en contactarnos. <br> En BlueyVet encuentras los mejores precios</p>
            <p>&copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.</p>
        </div>
    </div>
</body>

</html>