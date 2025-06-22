@component('mail::message')
# Reporte Mensual de Citas

Estimado/a,

Adjunto encontrará el reporte de citas correspondientes al mes de **{{ $monthName }} de {{ $year }}**.

Este reporte incluye todas las citas registradas en el sistema para dicho período.

Gracias,
{{ config('app.name') }}
@endcomponent