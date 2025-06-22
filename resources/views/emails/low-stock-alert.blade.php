<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Stock Bajo - {{ $product->name }}</title>
    <style>
        /* Aquí va tu CSS como en tu otra plantilla */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; text-align: center; margin-bottom: 20px; } /* Rojo para alerta */
        p { margin-bottom: 10px; line-height: 1.5; }
        .panel { background-color: #ffebe6; padding: 15px; border-left: 5px solid #dc3545; margin-top: 20px; } /* Panel de alerta */
        .button { display: inline-block; background-color: #007bff; color: #ffffff !important; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; text-align: center; }
        .footer { text-align: center; margin-top: 30px; font-size: 0.8em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Alerta de Stock Bajo!</h1>

        <p>Estimado/a Administrador,</p>

        <p>El stock del producto <strong>{{ $product->name }}</strong> ha caído por debajo de su umbral definido.</p>

        <div class="panel">
            <p><strong>Detalles del Producto:</strong></p>
            <ul>
                <li><strong>Nombre:</strong> {{ $product->name }}</li>
                <li><strong>Descripción:</strong> {{ $product->description }}</li>
                <li><strong>Stock Actual:</strong> {{ $product->stock }} unidades</li>
                <li><strong>Umbral Mínimo:</strong> {{ $product->min_stock_threshold }} unidades</li>
                <li><strong>Precio:</strong> S/.{{ number_format($product->price, 2) }}</li>
            </ul>
        </div>

        <p>Por favor, toma las medidas necesarias para reponer el inventario.</p>

        <p style="text-align: center;">
            <a href="{{ $url }}" class="button">Ver Producto en Filament</a>
        </p>

        <p>Atentamente,<br>
        Tu Equipo de Gestión de Inventario de BlueyVet</p>

        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a esta dirección.</p>
            <p>&copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>