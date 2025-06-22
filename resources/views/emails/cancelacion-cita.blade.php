<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cancelación de cita</title>
</head>
<body>
    <h2>Solicitud de cancelación de cita</h2>

    <p>
        El veterinario
        <strong>{{ $appointment->veterinarian->user->name ?? 'No disponible' }}</strong>
        ha solicitado cancelar una cita.
    </p>

    <p><strong>Mascota:</strong> {{ $appointment->mascota->name ?? 'No disponible' }}</p>
    <p><strong>Fecha:</strong> {{ $appointment->date }}</p>
    <p><strong>Motivo:</strong> {{ $motivo }}</p>

    <p>Esta solicitud ha sido registrada correctamente.</p>
</body>
</html>
