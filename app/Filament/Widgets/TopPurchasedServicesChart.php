<?php

namespace App\Filament\Widgets;

use App\Models\ServiceOrder;
use App\Models\Service;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend; // Asegúrate de que sea Flowframe\LaravelTrend\Trend
use Flowframe\Trend\TrendValue; // Asegúrate de que sea Flowframe\LaravelTrend\TrendValue
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TopPurchasedServicesChart extends ChartWidget
{
    protected static ?string $heading = 'Tendencia de Compras de Servicios Principales';
    protected static ?int $sort = 0; // Para que aparezca primero
    protected static string $color = '#4F46E5'; // Un color vibrante para el énfasis del widget (Indigo 600)
    protected int | string | array $columnSpan = 'full'; // Para que ocupe todo el ancho
    protected int | string | array $contentHeight = '350px'; // Un poco más de altura para un mejor aspecto
    public ?string $filter = 'month'; // Default filter to show last month

    // Paleta de colores personalizada y moderna (inspirada en Tailwind/Google)
    private array $customPalette = [
        '#4F46E5', // Indigo 600
        '#EF4444', // Red 500
        '#22C55E', // Green 500
        '#FBBF24', // Amber 400
        '#8B5CF6', // Violet 500
        '#EC4899', // Pink 500
        '#06B6D4', // Cyan 500
        '#F97316', // Orange 500
        '#A855F7', // Purple 500
        '#3B82F6', // Blue 500
    ];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = match ($this->filter) {
            'today' => Carbon::today()->startOfDay(),
            'week' => Carbon::now()->subWeek()->startOfDay(),
            'month' => Carbon::now()->subMonth()->startOfDay(),
            'year' => Carbon::now()->subYear()->startOfDay(),
            default => Carbon::now()->subMonth()->startOfDay(),
        };
        $endDate = Carbon::now()->endOfDay();

        $topServiceIds = ServiceOrder::query()
            ->select('service_id', DB::raw('count(*) as total_purchases'))
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('service_id')
            ->orderByDesc('total_purchases')
            ->limit(5)
            ->pluck('service_id');

        $datasets = [];
        $labels = [];

        foreach ($topServiceIds as $index => $serviceId) {
            $serviceName = Service::find($serviceId)?->name ?? "Servicio (ID: {$serviceId})";

            $trendPeriod = match ($this->filter) {
                'today' => 'perHour',
                'week' => 'perDay',
                'month' => 'perDay',
                'year' => 'perMonth',
                default => 'perDay',
            };

            $query = ServiceOrder::query()
                ->where('service_id', $serviceId)
                ->where('status', 'completed');

            $trend = Trend::query($query)
                ->between($startDate, $endDate)
                ->{$trendPeriod}()
                ->count();

            $lineColor = $this->customPalette[$index % count($this->customPalette)]; // Usar la paleta personalizada

            $datasets[] = [
                'label' => $serviceName,
                'data' => $trend->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                'borderColor' => $lineColor,
                'backgroundColor' => $lineColor . '40', // Color con 25% de opacidad para un sutil relleno
                'fill' => 'origin', // Rellenar área debajo de la línea
                'tension' => 0.4,
                'pointRadius' => 5, // Puntos más grandes
                'pointBackgroundColor' => $lineColor,
                'pointBorderColor' => '#ffffff', // Borde blanco para los puntos
                'pointBorderWidth' => 2,
                'pointHoverRadius' => 7,
                'pointHoverBackgroundColor' => '#ffffff', // Fondo blanco al pasar el ratón
                'pointHoverBorderColor' => $lineColor,
                'pointHitRadius' => 10, // Área de detección de clic/hover
            ];

            if (empty($labels) && $trend->isNotEmpty()) {
                $labels = $trend->map(function (TrendValue $value) use ($trendPeriod) {
                    if ($trendPeriod === 'perHour') {
                        return Carbon::parse($value->date)->format('H:00');
                    }
                    return Carbon::parse($value->date)->format('M d');
                })->toArray();
            }
        }

        if (empty($datasets)) {
            return [
                'datasets' => [[
                    'label' => 'Sin Datos',
                    'data' => [0],
                    'borderColor' => '#ccc',
                    'backgroundColor' => '#ccc40',
                    'fill' => 'origin',
                    'tension' => 0.4,
                    'pointRadius' => 0, // No mostrar puntos si no hay datos
                ]],
                'labels' => [''],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hoy',
            'week' => 'Última Semana',
            'month' => 'Último Mes',
            'year' => 'Último Año',
        ];
    }

    // Esta función ya no se usa si usas customPalette directamente
    private function getRandomColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Cantidad de Compras',
                        'color' => '#6B7280', // Tailwind gray-500
                        'font' => [
                            'size' => 14,
                            'family' => 'Inter, sans-serif',
                            'weight' => '600', // Semi-bold
                        ],
                    ],
                    'ticks' => [
                        'color' => '#6B7280', // Tailwind gray-500
                        'font' => [
                            'size' => 12,
                        ],
                        'callback' => 'function(value) { return value; }', // Opcional: Personalizar el formato de los ticks
                    ],
                    'grid' => [
                        'color' => '#E5E7EB', // Tailwind gray-200, líneas de cuadrícula muy suaves
                        'drawBorder' => false, // No dibujar el borde del eje
                        'drawOnChartArea' => true, // Dibujar las líneas en el área del gráfico
                        'drawTicks' => false, // No dibujar los ticks pequeños en el borde
                        'tickBorderDash' => [2, 2], // Líneas punteadas
                    ],
                    'border' => [
                        'display' => false, // Ocultar la línea principal del eje
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Fecha / Hora',
                        'color' => '#6B7280',
                        'font' => [
                            'size' => 14,
                            'family' => 'Inter, sans-serif',
                            'weight' => '600',
                        ],
                    ],
                    'ticks' => [
                        'color' => '#6B7280',
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                    'grid' => [
                        'display' => false, // Normalmente ocultas las verticales para un look más limpio
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
                        'color' => '#374151', // Tailwind gray-700
                        'font' => [
                            'size' => 13,
                            'weight' => 'bold',
                            'family' => 'Inter, sans-serif',
                        ],
                        'boxWidth' => 20,
                        'padding' => 20,
                        'usePointStyle' => true, // Usar el estilo de punto de la línea en la leyenda
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => '#1F2937', // Tailwind gray-800
                    'titleColor' => '#F3F4F6', // Tailwind gray-100
                    'bodyColor' => '#D1D5DB', // Tailwind gray-300
                    'borderColor' => '#4B5563', // Tailwind gray-600
                    'borderWidth' => 1,
                    'cornerRadius' => 8, // Más redondeado
                    'padding' => 14,
                    'caretPadding' => 10, // Espacio entre el tooltip y el puntero
                    'displayColors' => true, // Mostrar la caja de color al lado del elemento
                    'boxPadding' => 4, // Relleno de la caja de color
                ],
                'title' => [
                    'display' => false,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'hover' => [
                'mode' => 'nearest',
                'intersect' => true,
            ],
            'animation' => [
                'duration' => 1500,
                'easing' => 'easeOutCubic', 
                'onComplete' => 'function() { /* console.log("Animación completa!"); */ }'
            ],
        ];
    }
}