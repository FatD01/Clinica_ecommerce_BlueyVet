<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Médico de {{ $mascota->name }}</title>
    <style>
        /* ESTILOS CSS para el PDF */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; line-height: 1.6; margin: 20px; }
        h1, h2, h3, h4, h5, h6 { color: #1e3a8a; margin-top: 20px; margin-bottom: 10px; }
        .container { width: 100%; margin: 0 auto; padding: 0; }
        .header { text-align: center; margin-bottom: 30px; }
        .header img { max-width: 100px; margin-bottom: 10px; } /* Si vas a incluir un logo */
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 8px; background-color: #f9f9f9; }
        .section h5 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px; color: #333; }
        .record { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; background-color: #fff; }
        .record p { margin-bottom: 5px; }
        .record strong { color: #555; }
        .footer { text-align: center; margin-top: 50px; font-size: 8pt; color: #777; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Historial Médico BlueyVet</h1>
        <h3>Mascota: {{ $mascota->name }} ({{ $mascota->species }} - {{ $mascota->race }})</h3>
        <p>Fecha de Generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section">
        <h5>Datos del Dueño</h5>
        <p><strong>Nombre:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
        <p><strong>Email:</strong> {{ $usuario->email ?? 'No disponible' }}</p>
        <p><strong>Teléfono:</strong> {{ $cliente->telefono ?? 'No registrado' }}</p>
        <p><strong>Dirección:</strong> {{ $cliente->direccion ?? 'No registrada' }}</p>
    </div>

    <div class="section">
        <h5>Datos de la Mascota</h5>
        <p><strong>Nombre:</strong> {{ $mascota->name }}</p>
        <p><strong>Especie:</strong> {{ $mascota->species }}</p>
        <p><strong>Raza:</strong> {{ $mascota->race }}</p>
        <p><strong>Peso:</strong> {{ $mascota->weight }} kg</p>
        <p><strong>Nacimiento:</strong> {{ \Carbon\Carbon::parse($mascota->birth_date)->format('d/m/Y') }}</p>
        <p><strong>Alergias:</strong> {{ $mascota->allergies ?? 'Ninguna' }}</p>
        @if($mascota->getFirstMediaUrl('avatars'))
            @elseif($mascota->image)
            @endif
    </div>

    <div class="section">
        <h5>Consultas Médicas (Historial)</h5>
        @forelse ($registros as $registro)
            <div class="record">
                <h6>Consulta #{{ $loop->iteration }} - Fecha: {{ \Carbon\Carbon::parse($registro->consultation_date)->format('d/m/Y') }}</h6>
                <ul>
                    <li><strong>Veterinario:</strong> {{ $registro->veterinarian->user->name ?? 'N/A' }}</li>
                    <li><strong>Servicio:</strong> {{ $registro->service->name ?? 'N/A' }}</li>
                    <li><strong>Razón de consulta:</strong> {{ $registro->reason_for_consultation ?? 'No registrada' }}</li>
                    <li><strong>Diagnóstico:</strong> {{ $registro->diagnosis ?? 'No registrado' }}</li>
                    <li><strong>Tratamiento:</strong> {{ $registro->treatment ?? 'No registrado' }}</li>
                    <li><strong>Prescripción:</strong> {{ $registro->prescription ?? 'No registrada' }}</li>
                    <li><strong>Observaciones:</strong> {{ $registro->observations ?? 'Sin observaciones' }}</li>
                    <li><strong>Notas:</strong> {{ $registro->notes ?? 'Sin notas' }}</li>
                </ul>
            </div>
        @empty
            <p>No hay registros médicos disponibles en el rango de fechas seleccionado.</p>
        @endforelse
    </div>

    <div class="footer">
        <p>Documento generado por el sistema BlueyVet.</p>
        <p>&copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.</p>
    </div>
</body>
</html>