<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud de Cita - BlueyVet</title>
    <style>
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
        <h1>¡Nueva Solicitud de Cita por Correo!</h1>

        <p>Hola, hemos recibido una nueva solicitud de cita a través del formulario de servicios:</p>

        <div class="highlight">
            <p><strong>Nombres:</strong> {{ $formData['nombres'] }} {{ $formData['apellidos'] }}</p>
            <p><strong>Correo:</strong> <a href="mailto:{{ $formData['email'] }}">{{ $formData['email'] }}</a></p>
            <p><strong>Teléfono:</strong> {{ $formData['telefono'] }}</p>
            <p><strong>Servicio Solicitado:</strong> {{ $formData['servicio'] }}</p>
            <p><strong>Veterinario Preferido:</strong> {{ $formData['veterinario'] }}</p>
            <p><strong>Fecha Deseada:</strong> {{ \Carbon\Carbon::parse($formData['fecha'])->format('d/m/Y') }}</p>
            <p><strong>Hora Deseada:</strong> {{ \Carbon\Carbon::parse($formData['hora'])->format('H:i') }}</p>
            @if (!empty($formData['mensaje']))
                <p><strong>Mensaje Adicional:</strong> {{ $formData['mensaje'] }}</p>
            @endif
        </div>

        <p>Por favor, contacta al cliente para confirmar los detalles de la cita.</p>

        <p style="text-align: center;">
            <a href="mailto:{{ $formData['email'] }}" class="button">Responder al Cliente</a>
        </p>

        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a esta dirección.</p>
            <p>&copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>