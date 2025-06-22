<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Citas</title> {{-- Cambiado de Órdenes de Servicio para reflejar el contenido --}}
    <style>
        /* Define la fuente predeterminada y sus respaldos */
        body {
            font-family: 'Inter', sans-serif; /* Si 'Inter' no carga, usará una sans-serif genérica */
            margin: 0; /* Reinicia los márgenes del cuerpo */
            padding: 40px; /* Aplica el padding deseado al cuerpo */
            line-height: 1.6;
            color: #1f2937;
            position: relative; /* Necesario para el footer absoluto */
            min-height: 297mm; /* Altura mínima de una A4 para asegurar el footer en la parte inferior */
        }

        /* Encabezado y logo */
        .header-section {
            overflow: auto; /* Para limpiar el float */
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .company-logo {
            width: 150px; /* Ajusta el tamaño según tu logo */
            height: auto;
            float: left; /* Para que el título y fecha se alineen a la derecha */
            margin-right: 20px;
        }

        .header-content {
            overflow: hidden; /* Contiene el resto del contenido del encabezado */
        }

        h1 {
            font-size: 32px;
            font-weight: 600;
            text-align: center; /* Alinea el título centralmente dentro de su contenedor */
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
            font-size: 13px;
            color: #374151;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px 20px;
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
            border-collapse: collapse;
            font-size: 14px;
            border-radius: 8px;
            overflow: hidden; /* Esto es importante para que border-radius funcione en tablas */
            margin-top: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        thead {
            background-color: #e0f2fe; /* Azul claro para el encabezado de la tabla */
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 700; /* Más negrita para los encabezados */
            color: #0369a1; /* Azul más oscuro */
            border-bottom: 2px solid #a7d9f7;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top; /* Alineación superior para el contenido de la celda */
        }

        /* Estilos para las filas alternas */
        tr:nth-child(even) {
            background-color: #ffffff;
        }
        tr:nth-child(odd) {
            background-color: #f9fafb;
        }

        /* Estilos para la columna de Monto y Fecha */
        /* NOTA: Estos selectores ":nth-child()" dependen del orden de las columnas.
           Si cambias el orden de tus <th> en la tabla, es posible que necesites
           ajustar estos selectores CSS. */
        td:nth-child(4) { /* Fecha y Hora de la Cita */
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
        td:nth-child(5) { /* Motivo */
            text-align: left;
        }
        td:nth-child(6) { /* Estado */
            text-align: center;
        }


        /* Estilos para el span de estado (badge) */
        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block; /* Para asegurar que el padding y border-radius se apliquen correctamente */
            min-width: 75px; /* Ancho mínimo para badges uniformes */
            text-align: center;
        }

        /* Colores de los badges */
        .status-pending { background-color: #fef9c3; color: #92400e; } /* Amarillo para pendiente */
        .status-confirmed { background-color: #d1fae5; color: #065f46; } /* Verde para confirmado */
        .status-completed { background-color: #bfdbfe; color: #1e40af; } /* Azul para completado */
        .status-cancelled { background-color: #fee2e2; color: #991b1b; } /* Rojo (similar a failed) para cancelado */
        .status-default { background-color: #e2e3e5; color: #383d41; } /* Gris por defecto para estados no definidos */

        /* Resumen al final de la tabla */
        .table-summary {
            margin-top: 20px;
            font-size: 14px;
            text-align: right;
            color: #1f2937;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
        }
        .table-summary p {
            margin: 5px 0;
            padding: 0;
            text-align: right; /* Asegura que estos p se alineen a la derecha */
        }
        .table-summary strong {
            color: #111827;
        }

        /* Pie de página (se ubicará al final de cada página si es multipágina) */
        .footer {
            position: absolute;
            bottom: 40px; /* Ajusta este valor para la distancia desde la parte inferior */
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            margin: 0 40px; /* Misma margin que el padding del body */
        }
    </style>
</head>
<body>
    <div class="header-section">
        <img src="{{ public_path('img/logo-blueyvet.png') }}" class="company-logo" alt="Logo de BlueyVet">
        <div class="header-content">
            <h1>Listado de Citas</h1> {{-- Título ajustado para Citas --}}
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
                <th>Mascota</th> {{-- Cambiado de Cliente --}}
                <th>Veterinario</th> {{-- Cambiado de Servicio --}}
                <th>Fecha y Hora</th> {{-- Nueva columna para la fecha y hora de la cita --}}
                <th>Motivo</th> {{-- Nueva columna para el motivo --}}
                <th>Estado</th>
                <th>Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment) {{-- Se cambió $orders a $appointments y $item a $appointment --}}
                <tr>
                    <td>{{ $appointment->id }}</td>
                    <td>{{ $appointment->mascota->name ?? '—' }}</td> {{-- Acceso a la relación mascota --}}
                    <td>{{ $appointment->veterinarian->user->name ?? '—' }}</td> {{-- Acceso a la relación veterinarian y user --}}
                    <td>
                        {{ $appointment->date ? Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td>{{ $appointment->reason ?? '—' }}</td> {{-- Muestra el motivo --}}
                    <td>
                        <span class="status-badge
                            @switch(strtolower($appointment->status)) {{-- Se usa $appointment->status --}}
                                @case('pending') status-pending @break
                                @case('confirmed') status-confirmed @break {{-- Nuevo estado --}}
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
        <p><strong>Total de Citas:</strong> {{ $total_appointments }}</p> {{-- Se usó $total_appointments --}}
        {{-- Se eliminó el "Monto Total" ya que no es aplicable a citas por defecto. --}}
        {{-- Si quisieras un resumen por estado de citas, aquí sería el lugar para añadirlo. --}}
        @php
            // Esto es opcional, si quieres añadir un desglose por estados al resumen
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
        {{-- Puedes añadir aquí más información de contacto si lo deseas --}}
        {{-- <p>Contacto: info@blueyvet.com | +51 987 654 321</p> --}}
    </div>
</body>
</html>