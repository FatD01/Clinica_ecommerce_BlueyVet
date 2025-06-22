<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Historial Médico de {{ $mascota->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Para soporte de caracteres especiales */
            font-size: 10px;
            line-height: 1.6;
            color: #333;
        }
        h1, h2, h3, h4 {
            color: #1a1a1a;
            margin-top: 0;
            margin-bottom: 10px;
        }
        h1 { font-size: 20px; text-align: center; margin-bottom: 20px; }
        h2 { font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;}
        h3 { font-size: 14px; color: #555;}
        p { margin: 0 0 5px 0; }
        .section {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .record-item {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            background-color: #fff;
        }
        .record-item:last-child {
            margin-bottom: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            font-size: 8px;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Historial Médico Completo de {{ $mascota->name }}</h1>
        <p>Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section">
        <h2>Datos de la Mascota</h2>
        <p><strong>Nombre:</strong> {{ $mascota->name }}</p>
        <p><strong>Especie:</strong> {{ $mascota->species }}</p>
        <p><strong>Raza:</strong> {{ $mascota->breed ?? 'N/A' }}</p>
        <p><strong>Fecha de Nacimiento:</strong> {{ \Carbon\Carbon::parse($mascota->birth_date)->format('d/m/Y') }}</p>
        <p><strong>Género:</strong> {{ $mascota->gender }}</p>
        <p><strong>Estado:</strong> {{ $mascota->status ?? 'N/A' }}</p>
    </div>

    @if ($cliente)
        <div class="section">
            <h2>Datos del Propietario</h2>
            <p><strong>Nombre:</strong> {{ $cliente->name }}</p>
            <p><strong>Email:</strong> {{ $cliente->email }}</p>
            <p><strong>Teléfono:</strong> {{ $cliente->client->phone_number ?? 'N/A' }}</p>
            <p><strong>Dirección:</strong> {{ $cliente->client->address ?? 'N/A' }}</p>
        </div>
    @endif

    <h2>Consultas Médicas</h2>
    @forelse ($historiales as $historial)
        <div class="record-item">
            <h3>Registro #{{ $historial->id }} - {{ \Carbon\Carbon::parse($historial->consultation_date)->format('d/m/Y H:i') }}</h3>
            <p><strong>Veterinario:</strong> {{ $historial->veterinarian->user->name ?? 'N/A' }}</p>
            <p><strong>Servicio:</strong> {{ $historial->service->name ?? 'N/A' }}</p>
            <p><strong>Razón de consulta:</strong> {{ $historial->reason_for_consultation ?? 'No registrada' }}</p>
            <p><strong>Diagnóstico:</strong> {{ $historial->diagnosis ?? 'No registrado' }}</p>
            <p><strong>Tratamiento:</strong> {{ $historial->treatment ?? 'No registrado' }}</p>
            <p><strong>Prescripción:</strong> {{ $historial->prescription ?? 'No registrada' }}</p>
            <p><strong>Observaciones:</strong> {{ $historial->observations ?? 'Sin observaciones' }}</p>
        </div>
    @empty
        <p>No hay registros médicos para esta mascota.</p>
    @endforelse

    <div class="footer">
        Página <span class="page-number"></span> de <span class="total-pages"></span>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>