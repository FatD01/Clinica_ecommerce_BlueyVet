<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Mascota;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentsChart extends ChartWidget
{
    protected static ?string $heading = 'Citas por Especie de Mascota';

    // Ajusta este sort. Si tu primer gráfico tiene sort = 0, y el AccountWidget tiene sort = 1 (y ambos son full-width),
    // este debería tener un sort mayor para aparecer debajo, por ejemplo, 2 o más.
    protected static ?int $sort = 2; // O 10, dependiendo de cómo quieras ordenarlo después de los full-width

    protected static string $color = 'primary'; // Puedes dejar 'primary' o cambiarlo a un color Tailwind como 'success'

    // Si quieres que ocupe todo el ancho, descomenta la línea de abajo.
    // protected int | string | array $columnSpan = 'full';
    // Si quieres que vaya en una columna al lado de otro, por ejemplo, '1/2'.
    // Si tu otro gráfico ya es full, déjalo sin $columnSpan o en '1/2' si tienes espacio.

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Todas las Fechas',
            'today' => 'Hoy',
            'week' => 'Esta Semana',
            'month' => 'Este Mes',
            'year' => 'Este Año',
            'last_month' => 'Mes Pasado',
            'last_year' => 'Año Pasado',
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Se mantiene el tipo 'pie' original
    }

    protected function getData(): array
    {
        $query = DB::table('appointments')
            ->join('mascotas', 'appointments.mascota_id', '=', 'mascotas.id')
            ->select('mascotas.species', DB::raw('count(*) as count'));

        switch ($this->filter) {
            case 'today':
                $query->whereDate('appointments.date', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('appointments.date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('appointments.date', Carbon::now()->month)
                      ->whereYear('appointments.date', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('appointments.date', Carbon::now()->year);
                break;
            case 'last_month':
                $query->whereMonth('appointments.date', Carbon::now()->subMonth()->month)
                      ->whereYear('appointments.date', Carbon::now()->subMonth()->year);
                break;
            case 'last_year':
                $query->whereYear('appointments.date', Carbon::now()->subYear()->year);
                break;
            case 'all':
            default:
                break;
        }

        $appointmentCounts = $query->groupBy('mascotas.species')->get();

        $labels = [];
        $data = [];
        $backgroundColors = [];

        // --- INICIO DE CAMBIO DE COLORES ---
        // Paleta de colores coherente:
        $colorsMap = [
            'perro' => '#4F46E5', // Indigo 600 para Perros
            'gato' => '#EF4444',  // Red 500 para Gatos
            'otro' => '#6B7280',  // Gray 500 para Otros
        ];
        // --- FIN DE CAMBIO DE COLORES ---

        $perroCount = 0;
        $gatoCount = 0;
        $otherCount = 0;

        foreach ($appointmentCounts as $item) {
            $species = strtolower($item->species);

            if ($species === 'perro') {
                $perroCount += $item->count;
            } elseif ($species === 'gato') {
                $gatoCount += $item->count;
            } else {
                $otherCount += $item->count;
            }
        }

        if ($perroCount > 0) {
            $labels[] = 'Perro';
            $data[] = $perroCount;
            $backgroundColors[] = $colorsMap['perro'];
        }
        if ($gatoCount > 0) {
            $labels[] = 'Gato';
            $data[] = $gatoCount;
            $backgroundColors[] = $colorsMap['gato'];
        }
        if ($otherCount > 0) {
            $labels[] = 'Otro';
            $data[] = $otherCount;
            $backgroundColors[] = $colorsMap['otro']; // Usamos el color 'otro' de la paleta
        }

        if (empty($data)) {
            $labels = ['No hay citas registradas para este periodo'];
            $data = [1];
            $backgroundColors = ['#DDDDDD'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Número de Citas',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'position' => 'bottom',
                'labels' => [
                    'boxWidth' => 20,
                    // Si quieres que las etiquetas de la leyenda tengan el mismo color de texto
                    // que en el otro gráfico:
                    'color' => '#374151', // Tailwind gray-700
                    'font' => [
                        'size' => 13,
                        'weight' => 'bold',
                        'family' => 'Inter, sans-serif',
                    ],
                ]
            ],
            // Si quieres mejorar el tooltip para que muestre porcentajes (como el ejemplo anterior),
            // puedes añadir la sección 'tooltip' aquí. De lo contrario, se usará el predeterminado.
            // 'tooltip' => [
            //     'mode' => 'point',
            //     'intersect' => false,
            //     'backgroundColor' => '#1F2937', // Tailwind gray-800
            //     'titleColor' => '#F3F4F6', // Tailwind gray-100
            //     'bodyColor' => '#D1D5DB', // Tailwind gray-300
            //     'borderColor' => '#4B5563', // Tailwind gray-600
            //     'borderWidth' => 1,
            //     'cornerRadius' => 8,
            //     'padding' => 14,
            //     'caretPadding' => 10,
            //     'displayColors' => true,
            //     'boxPadding' => 4,
            //     'callbacks' => [
            //         'label' => 'function(context) {
            //             let sum = 0;
            //             let dataArr = context.chart.data.datasets[0].data;
            //             dataArr.map(data => {
            //                 sum += data;
            //             });
            //             let percentage = (context.parsed / sum * 100).toFixed(2) + "%";
            //             return context.label + ": " + context.parsed + " (" + percentage + ")";
            //         }'
            //     ]
            // ],
        ],
        'responsive' => true,
        'maintainAspectRatio' => false,
        // Puedes añadir una animación sencilla para que no aparezca de golpe:
        'animation' => [
            'duration' => 1000, // 1 segundo
            'easing' => 'easeOutCubic',
            'animateRotate' => true, // Animación de rotación para pastel
            'animateScale' => false, // No animar escala para pastel
        ],
    ];
}