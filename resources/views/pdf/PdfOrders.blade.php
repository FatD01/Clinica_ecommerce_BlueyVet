<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Órdenes de Productos</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 40px;
            line-height: 1.6;
            color: #1f2937;
            position: relative;
            min-height: 297mm;
        }
        .header-section {
            overflow: auto;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .company-logo {
            width: 150px;
            height: auto;
            float: left;
            margin-right: 20px;
        }
        .header-content {
            overflow: hidden;
        }
        h1 {
            font-size: 32px;
            font-weight: 600;
            text-align: center;
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
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        thead {
            background-color: #e0f2fe;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: #0369a1;
            border-bottom: 2px solid #a7d9f7;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #ffffff;
        }
        tr:nth-child(odd) {
            background-color: #f9fafb;
        }
        td:nth-child(4) { /* Columna de Monto/Total */
            text-align: right;
            font-weight: 500;
        }
        td:nth-child(5) { /* Columna de Estado */
            text-align: center;
        }
        td:nth-child(6) { /* Columna de Fecha Creación */
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            min-width: 75px;
            text-align: center;
        }
        .status-pending { background-color: #fef9c3; color: #92400e; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .status-failed { background-color: #fee2e2; color: #991b1b; }
        .status-refunded { background-color: #e0f2fe; color: #0369a1; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        .status-processing { background-color: #dbeafe; color: #1e40af; }
        .status-shipped { background-color: #bfdbfe; color: #1d4ed8; }
        .status-default { background-color: #e2e3e5; color: #383d41; }

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
            text-align: right;
        }
        .table-summary strong {
            color: #111827;
        }
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
            margin: 0 40px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <img src="{{ public_path('img/logo-blueyvet.png') }}" class="company-logo" alt="Logo de BlueyVet">
        <div class="header-content">
            <h1>Reporte de Órdenes de Productos</h1>
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
                <th>ID Orden</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha Creación</th>
                <th>Items (Cantidad)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->user->name ?? '—' }}</td>
                    <td>S/ {{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        <span class="status-badge
                            @switch(strtolower($order->status))
                                @case('pending') status-pending @break
                                @case('completed') status-completed @break
                                @case('failed') status-failed @break
                                @case('refunded') status-refunded @break
                                @case('cancelled') status-cancelled @break
                                @case('processing') status-processing @break
                                @case('shipped') status-shipped @break
                                @default status-default @break
                            @endswitch">
                            {{ ucfirst(strtolower($order->status)) }}
                        </span>
                    </td>
                    <td>
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        @forelse($order->Items as $item)
                            {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }})<br>
                        @empty
                            Sin ítems
                        @endforelse
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-summary">
        <p><strong>Total de Órdenes:</strong> {{ $total_orders }}</p>
        <p><strong>Monto Total de Ventas:</strong> S/ {{ number_format($total_amount, 2) }}</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.</p>
    </div>
</body>
</html>