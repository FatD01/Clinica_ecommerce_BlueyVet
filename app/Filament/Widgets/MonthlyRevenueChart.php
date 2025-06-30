<?php

namespace App\Filament\Widgets; // Asegúrate de que el namespace sea correcto

use Filament\Widgets\ChartWidget;
use App\Models\OrderItem; // Para productos
use App\Models\ServiceOrder; // Para servicios
use Carbon\Carbon;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Ingresos Mensuales: Productos vs. Servicios';

    protected static string $chart = 'line';

    protected static ?int $sort = 0;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // 1. Obtener ingresos por productos
        $productRevenueData = OrderItem::query()
            ->selectRaw('DATE_FORMAT(order_items.created_at, "%Y-%m") as month, SUM(order_items.quantity * order_items.price) as total_revenue')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // 2. Obtener ingresos por servicios
        $serviceRevenueData = ServiceOrder::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total_revenue')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $labels = [];
        $productDataPoints = [];
        $serviceDataPoints = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMonth()) {
            $monthKey = $date->format('Y-m');
            $labels[] = $date->isoFormat('MMM YYYY'); // Incluimos el año para mayor claridad

            $productDataPoints[] = $productRevenueData->has($monthKey)
                ? (float) $productRevenueData[$monthKey]->total_revenue
                : 0;

            $serviceDataPoints[] = $serviceRevenueData->has($monthKey)
                ? (float) $serviceRevenueData[$monthKey]->total_revenue
                : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos por Productos (S/)',
                    'data' => $productDataPoints,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.4)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                    'fill' => 'origin', // Usamos 'origin' para un relleno estándar
                    'tension' => 0.4,
                    'pointRadius' => 5, // Haz los puntos visibles
                    'pointBackgroundColor' => 'rgba(75, 192, 192, 1)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointHoverRadius' => 7,
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => 'rgba(75, 192, 192, 1)',
                ],
                [
                    'label' => 'Ingresos por Servicios (S/)',
                    'data' => $serviceDataPoints,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.4)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 2,
                    'fill' => 'origin',
                    'tension' => 0.4,
                    'pointRadius' => 5, // Haz los puntos visibles
                    'pointBackgroundColor' => 'rgba(255, 99, 132, 1)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointHoverRadius' => 7,
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => 'rgba(255, 99, 132, 1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return static::$chart;
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [ // Añadimos un título al eje Y para mayor claridad
                        'display' => true,
                        'text' => 'Ingresos (S/)',
                        'color' => '#6B7280',
                        'font' => [ 'size' => 14, 'weight' => '600' ],
                        'padding' => ['bottom' =>30],
                    ],
                    
                    'ticks' => [
                        'color' => '#6B7280',
                        'font' => [ 'size' => 12 ],
                        // Utilizamos un callback más sencillo o confiamos en el default de Chart.js si no necesitamos lógica compleja.
                        // Mantengo tu callback con parseFloat para seguridad, pero sin if(null).
                        'callback' => 'function(value) { return "S/ " + parseFloat(value).toFixed(2); }',
                        'precision' => 2,
                    ],
                    'grid' => [
                        'color' => 'rgba(200, 200, 200, 0.2)', // Líneas de cuadrícula suaves
                        'drawBorder' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Fecha',
                        'color' => '#6B7280',
                        'font' => [ 'size' => 14, 'weight' => '600' ],
                    ],
                    'ticks' => [
                        'color' => '#6B7280',
                        'font' => [ 'size' => 12 ],
                    ],
                    'grid' => [
                        'display' => false, // Ocultar líneas verticales
                        'drawBorder' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'align' => 'end', // Alinear leyenda a la derecha
                    'labels' => [
                        'color' => '#374151',
                        'font' => [ 'size' => 13, 'weight' => 'bold' ],
                        'boxWidth' => 20,
                        'padding' => 20,
                        'usePointStyle' => true, // Usa el estilo de los puntos de la línea en la leyenda
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    // **QUITAMOS EL CALLBACK PERSONALIZADO POR AHORA**
                    // Dejamos que Chart.js use su callback por defecto para ver si funciona.
                    // Si funciona, luego podemos personalizarlo.
                    'backgroundColor' => '#1F2937',
                    'titleColor' => '#F3F4F6',
                    'bodyColor' => '#D1D5DB',
                    'borderColor' => '#4B5563',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'padding' => 14,
                    'caretPadding' => 10,
                    'displayColors' => true,
                    'boxPadding' => 4,
                ],
                'title' => [ // Si quieres un título dentro del área del gráfico (aparte del heading del widget)
                    'display' => false, // O true si lo deseas
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'hover' => [ // Copiado de tu gráfico que sí funciona
                'mode' => 'nearest',
                'intersect' => true,
            ],
            'animation' => [ // Copiado de tu gráfico que sí funciona
                'duration' => 1500,
                'easing' => 'easeOutCubic',
                // 'onComplete' => 'function() { /* console.log("Animación completa!"); */ }'
            ],
        ];
    }
}