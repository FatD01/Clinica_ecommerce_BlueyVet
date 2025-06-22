<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cita Médica - {{ $registro->mascota->name ?? 'N/A' }} - {{ \Carbon\Carbon::parse($registro->consultation_date)->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 20px;
        }
        h1, h2, h3 {
            color: #1a1a1a;
            margin-top: 0;
            margin-bottom: 10px;
            text-align: center;
        }
        h1 { font-size: 24px; margin-bottom: 25px; }
        h2 { font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-top: 20px;}
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        p { margin: 0 0 8px 0; }
        strong { color: #000; }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Detalle de Cita Médica</h1>

    <div class="section">
        <h2>Información de la Cita</h2>
        <p><strong>Fecha de Consulta:</strong> {{ \Carbon\Carbon::parse($registro->consultation_date)->format('d/m/Y H:i') }}</p>
        <p><strong>Veterinario:</strong> {{ $registro->veterinarian->user->name ?? 'N/A' }}</p>
        <p><strong>Servicio:</strong> {{ $registro->service->name ?? 'N/A' }}</p>
        <p><strong>Razón de consulta:</strong> {{ $registro->reason_for_consultation ?? 'No registrada' }}</p>
    </div>

    <div class="section">
        <h2>Detalles Médicos</h2>
        <p><strong>Diagnóstico:</strong> {{ $registro->diagnosis ?? 'No registrado' }}</p>
        <p><strong>Tratamiento:</strong> {{ $registro->treatment ?? 'No registrado' }}</p>
        <p><strong>Prescripción:</strong> {{ $registro->prescription ?? 'No registrada' }}</p>
        <p><strong>Observaciones:</strong> {{ $registro->observations ?? 'Sin observaciones' }}</p>
    </div>

    <div class="section">
        <h2>Información de la Mascota</h2>
        <p><strong>Nombre:</strong> {{ $registro->mascota->name ?? 'N/A' }}</p>
        <p><strong>Especie:</strong> {{ $registro->mascota->species ?? 'N/A' }}</p>
        <p><strong>Raza:</strong> {{ $registro->mascota->breed ?? 'N/A' }}</p>
        <p><strong>Propietario:</strong> {{ $registro->mascota->cliente->user->name ?? 'N/A' }}</p>
    </div>

    <div class="footer">
        Documento generado automáticamente por BlueyVet.
    </div>
</body>
</html>