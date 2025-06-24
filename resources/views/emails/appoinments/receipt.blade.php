<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - BlueyVet</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #28a745; /* Color verde para recibo de pago */
            padding-bottom: 15px;
        }
        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .section-title {
            color: #28a745;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .details-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .details-row {
            display: table-row;
        }
        .details-label, .details-value {
            display: table-cell;
            padding: 8px 0;
            vertical-align: top;
        }
        .details-label {
            font-weight: bold;
            width: 35%;
            color: #555;
        }
        .details-value {
            width: 65%;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .amount-due {
            font-size: 22px;
            font-weight: bold;
            color: #28a745;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if (file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="BlueyVet Logo" class="logo">
            @endif
            <h1>Recibo de Pago BlueyVet</h1>
            <p>Confirmación de tu transacción</p>
        </div>

        <div class="section-title">Información del Pago</div>
        <div class="details-grid">
            <div class="details-row">
                <div class="details-label">Número de Orden:</div>
                <div class="details-value">#{{ $cita->serviceOrder->id }}</div> {{-- Usar el ID de la ServiceOrder --}}
            </div>
            <div class="details-row">
                <div class="details-label">Fecha de Pago:</div>
                <div class="details-value">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
            </div>
            <div class="details-row">
                <div class="details-label">Cliente:</div>
                <div class="details-value">{{ $user->name }}</div>
            </div>
            <div class="details-row">
                <div class="details-label">Email:</div>
                <div class="details-value">{{ $user->email }}</div>
            </div>
            <div class="details-row">
                <div class="details-label">Método de Pago:</div>
                <div class="details-value">{{ $payment_method }}</div>
            </div>
            @if ($paypal_order_id)
            <div class="details-row">
                <div class="details-label">ID de Orden de PayPal:</div>
                <div class="details-value">{{ $paypal_order_id }}</div>
            </div>
            @endif
            @if ($transaction_id && $transaction_id != $paypal_order_id) {{-- Si son diferentes, mostrar ambos --}}
            <div class="details-row">
                <div class="details-label">ID de Transacción:</div>
                <div class="details-value">{{ $transaction_id }}</div>
            </div>
            @endif
        </div>

        <div class="section-title">Detalles del Servicio Adquirido</div>
        <div class="details-grid">
            <div class="details-row">
                <div class="details-label">Descripción:</div>
                <div class="details-value">Pago por el servicio de **{{ $service->name }}** para tu mascota **{{ $mascota->name }}**.</div>
            </div>
            <div class="details-row">
                <div class="details-label">Cita Asociada:</div>
                <div class="details-value">#{{ $cita->id }} - {{ $cita->start_time->format('d/m/Y H:i A') }}</div>
            </div>
        </div>

        <div class="amount-due">
            Monto Total Pagado: {{ $payment_amount }} {{ $payment_currency }}
        </div>

        <div class="footer">
            <p>Este es un comprobante de pago digital. No es necesaria una firma física.</p>
            <p>{{ config('app.name', 'BlueyVet') }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>