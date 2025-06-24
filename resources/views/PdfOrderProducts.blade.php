<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Citas</title> {{-- Cambiado de Órdenes de Servicio para reflejar el contenido --}}
    <style>
        /* Define la fuente predeterminada y sus respaldos */
        body {
            font-family: 'Inter', sans-serif;
            /* Si 'Inter' no carga, usará una sans-serif genérica */
            margin: 0;
            /* Reinicia los márgenes del cuerpo */
            padding: 25px;
            /* REDUCIDO DE 40px, Aumentado un poco de 20px para no ser tan agresivo */
            line-height: 1.6;
            color: #1f2937;
            position: relative;
            /* Necesario para el footer absoluto */
            min-height: 297mm;
            /* Altura mínima de una A4 para asegurar el footer en la parte inferior */
        }

        /* Encabezado y logo */
        .header-section {
            overflow: auto;
            /* Para limpiar el float */
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .company-logo {
            width: 150px;
            /* Ajusta el tamaño según tu logo */
            height: auto;
            float: left;
            /* Para que el título y fecha se alineen a la derecha */
            margin-right: 20px;
        }

        .header-content {
            overflow: hidden;
            /* Contiene el resto del contenido del encabezado */
        }

        h1 {
            font-size: 32px;
            font-weight: 600;
            text-align: center;
            /* Alinea el título centralmente dentro de su contenedor */
            margin-top: 0;
            margin-bottom: 10px;
            color: #111827;
        }

        .export-date {
            text-align: right;
            font-size: 14px;
            margin-bottom: 0;
            color: #4b5563;
        }

        /* Filtros Aplicados */
        .filters-applied {
            margin-top: 25px;
            margin-bottom: 30px;
            font-size: 12px;
            /* LIGERAMENTE REDUCIDO DE 13px */
            color: #374151;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
            /* LIGERAMENTE REDUCIDO DE 15px 20px */
        }

        .filters-applied p {
            text-align: left;
            font-weight: bold;
            margin-bottom: 8px;
            color: #1f2937;
        }

        .filters-applied ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .filters-applied li {
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .filters-applied li strong {
            color: #111827;
        }

        /* Tabla */
        table {
            width: 100%;
            /* Mantiene 100% para ocupar el espacio disponible */
            border-collapse: collapse;
            font-size: 13px;
            /* LIGERAMENTE REDUCIDO DE 14px */
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-left: auto;
            margin-right: auto;
        }

        thead {
            background-color: #e0f2fe;
            /* Azul claro para el encabezado de la tabla */
        }

        th {
            padding: 10px 8px;
            /* REDUCIDO DE 12px */
            text-align: left;
            font-weight: 700;
            color: #0369a1;
            border-bottom: 2px solid #a7d9f7;
            white-space: nowrap; /* Evita que los encabezados se rompan, a menos que sean muy largos */
        }

        td {
            padding: 8px 8px;
            /* REDUCIDO DE 10px 12px */
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        /* Estilos para las filas alternas */
        tr:nth-child(even) {
            background-color: #ffffff;
        }

        tr:nth-child(odd) {
            background-color: #f9fafb;
        }

        /* Estilos específicos de columna: ajustando el font-size para que el contenido de fecha y hora quepa */
        td:nth-child(4),
        /* Fecha y Hora de la Cita */
        td:nth-child(5) {
            /* Hora de finalización */
            text-align: center;
            font-size: 12px;
            /* LIGERAMENTE REDUCIDO DE 13px */
            color: #6b7280;
            white-space: nowrap; /* Mantiene la fecha/hora en una sola línea */
        }

        td:nth-child(6) {
            /* Motivo */
            text-align: left;
            white-space: normal;
            /* PERMITE QUE EL TEXTO SE ROMPA EN MÚLTIPLES LÍNEAS */
            word-wrap: break-word;
            /* Rompe palabras largas si no caben */
        }

        td:nth-child(7) {
            /* Estado */
            text-align: center;
        }

        td:nth-child(8) {
            /* Fecha Creación */
            text-align: center;
            font-size: 12px; /* LIGERAMENTE REDUCIDO */
            white-space: nowrap; /* Mantiene la fecha/hora en una sola línea */
        }

        /* Estilos para el span de estado (badge) */
        .status-badge {
            padding: 3px 6px;
            /* LIGERAMENTE REDUCIDO DE 4px 8px */
            border-radius: 6px;
            font-size: 11px;
            /* LIGERAMENTE REDUCIDO DE 12px */
            font-weight: 500;
            display: inline-block;
            min-width: 65px;
            /* LIGERAMENTE REDUCIDO DE 75px */
            text-align: center;
        }

        /* Colores de los badges */
        .status-pending {
            background-color: #fef9c3;
            color: #92400e;
        }

        .status-confirmed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-completed {
            background-color: #bfdbfe;
            color: #1e40af;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-default {
            background-color: #e2e3e5;
            color: #383d41;
        }

        /* Resumen al final de la tabla */
        .table-summary {
            margin-top: 20px;
            font-size: 13px;
            /* LIGERAMENTE REDUCIDO DE 14px */
            text-align: right;
            color: #1f2937;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
        }

        .table-summary p {
            margin: 5px 0;
            padding: 0;
            text-align: right;
        }

        .table-summary strong {
            color: #111827;
        }

        /* Pie de página (se ubicará al final de cada página si es multipágina) */
        .footer {
            position: absolute;
            bottom: 40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            margin: 0 25px; /* MISMO PADDING DEL BODY */
        }
    </style>
</head>

<body>
    <div class="header-section">
        <img src="{{ public_path('img/logo-blueyvet.png') }}" class="company-logo" alt="Logo de BlueyVet">
        <div class="header-content">
            <h1>Listado de Citas</h1>
            <p class="export-date">Fecha de exportación: <span>{{ $export_date }}</span></p>
        </div>
    </div>
    @if(!empty($active_filters))
    <div class="filters-applied">
        <p>Filtros aplicados:</p>
        <ul>
            @foreach($active_filters as $filter)
            <li>
                <strong>{{ $filter['label'] }}:</strong> {{ $filter['value'] }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Mascota</th>
                <th>Veterinario</th>
                <th>Fecha y Hora</th>
                <th>Hora fin</th> {{-- Texto más corto para el encabezado --}}
                <th>Motivo</th>
                <th>Estado</th>
                <th>Creación</th> {{-- Texto más corto para el encabezado --}}
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
            <tr>
                <td>{{ $appointment->id }}</td>
                <td>{{ $appointment->mascota->name ?? '—' }}</td>
                <td>{{ $appointment->veterinarian->user->name ?? '—' }}</td>
                <td>
                    {{ $appointment->date ? Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') : '—' }}
                </td>
                <td>
                    {{ $appointment->end_datetime ? Carbon\Carbon::parse($appointment->end_datetime)->format('H:i') : '—' }}
                </td>
                <td>{{ $appointment->reason ?? '—' }}</td>
                <td>
                    <span class="status-badge
                                @switch(strtolower($appointment->status))
                                    @case('pending') status-pending @break
                                    @case('confirmed') status-confirmed @break
                                    @case('completed') status-completed @break
                                    @case('cancelled') status-cancelled @break
                                    @default status-default @break
                                @endswitch">
                        {{ ucfirst(strtolower($appointment->status)) }}
                    </span>
                </td>
                <td>
                    {{ $appointment->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-summary">
        <p><strong>Total de Citas:</strong> {{ $total_appointments }}</p>
        @php
        $statusCounts = $appointments->groupBy('status')->map->count();
        @endphp
        @if($statusCounts->isNotEmpty())
        <p><strong>Citas por Estado:</strong></p>
        <ul>
            @foreach(['pending' => 'Pendientes', 'confirmed' => 'Confirmadas', 'completed' => 'Completadas', 'cancelled' => 'Canceladas'] as $key => $label)
            @if(isset($statusCounts[$key]))
            <li>{{ $label }}: {{ $statusCounts[$key] }}</li>
            @endif
            @endforeach
        </ul>
        @endif
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.</p>
    </div>
</body>

</html>