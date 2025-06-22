<!DOCTYPE html>
<html>
<head>
    <title>Comprobante de Compra - Orden #{{ $order->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .total { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Comprobante de Compra BlueyVet</h1>
        <p>Fecha: {{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div>
        <h3>Detalles del Cliente:</h3>
        <p>Nombre: {{ $order->user->name ?? 'N/A' }}</p>
        <p>Email: {{ $order->user->email ?? 'N/A' }}</p>
        <p>Orden ID: {{ $order->id }}</p>
        <p>Estado: {{ ucfirst($order->status) }}</p>
        <p>Dirección de Envio: {{ $order->customer_address }}</p>
    </div>

    <h3>Productos Comprados:</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 2) }} {{ $order->currency }}</td>
                    <td>{{ number_format($item->quantity * $item->price, 2) }} {{ $order->currency }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total">Total:</td>
                <td class="total">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>¡Gracias por tu compra en BlueyVet!</p>
        <p>Si tienes alguna pregunta, por favor contáctanos.</p>
    </div>
</body>
</html>