<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Mascota; // Asegúrate de que el modelo Mascota esté bien importado
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Importa Carbon para el manejo de fechas

class AppointmentsChart extends ChartWidget
{
    // Título del widget que se mostrará en el dashboard
    protected static ?string $heading = 'Citas por Especie de Mascota';

    // Orden en el que aparecerá el widget en el dashboard (opcional)
    protected static ?int $sort = 2;

    // Propiedad pública que Filament usará para el valor seleccionado del filtro
    public ?string $filter = 'all'; // Valor por defecto del filtro al cargar la página

    /**
     * Define los filtros de tiempo disponibles para el gráfico.
     * La clave es el valor interno que usaremos en la lógica, el valor es el texto a mostrar.
     */
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

    /**
     * Define el tipo de gráfico (bar, line, pie, doughnut, etc.).
     */
    protected function getType(): string
    {
        return 'pie'; // Para un gráfico circular (pastel)
    }

    /**
     * Prepara y retorna los datos para el gráfico.
     * Esta es la lógica principal que consulta la base de datos y formatea los datos.
     */
    protected function getData(): array
    {
        // 1. Inicia la consulta base para obtener citas y unirlas con mascotas
        $query = DB::table('appointments')
            ->join('mascotas', 'appointments.mascota_id', '=', 'mascotas.id')
            // Selecciona la columna 'species' de la tabla 'mascotas' y cuenta las citas
            ->select('mascotas.species', DB::raw('count(*) as count'));

        // 2. Aplica el filtro de fecha basado en el valor de $this->filter
        switch ($this->filter) {
            case 'today':
                $query->whereDate('appointments.date', Carbon::today());
                break;
            case 'week':
                // Filtra por citas dentro de la semana actual (de lunes a domingo)
                $query->whereBetween('appointments.date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                // Filtra por citas dentro del mes y año actual
                $query->whereMonth('appointments.date', Carbon::now()->month)
                      ->whereYear('appointments.date', Carbon::now()->year);
                break;
            case 'year':
                // Filtra por citas dentro del año actual
                $query->whereYear('appointments.date', Carbon::now()->year);
                break;
            case 'last_month':
                // Filtra por citas dentro del mes y año pasado
                $query->whereMonth('appointments.date', Carbon::now()->subMonth()->month)
                      ->whereYear('appointments.date', Carbon::now()->subMonth()->year);
                break;
            case 'last_year':
                // Filtra por citas dentro del año pasado
                $query->whereYear('appointments.date', Carbon::now()->subYear()->year);
                break;
            case 'all':
            default:
                // No se aplica filtro de fecha, se incluyen todas las citas
                break;
        }

        // 3. Ejecuta la consulta agrupando por especie
        $appointmentCounts = $query->groupBy('mascotas.species')->get();

        // Inicializa arrays para los datos del gráfico
        $labels = [];          // Etiquetas para cada rebanada (ej. 'Perro', 'Gato', 'Otro')
        $data = [];            // Valores numéricos para cada rebanada
        $backgroundColors = [];// Colores para cada rebanada

        // Define los colores específicos para 'perro' y 'gato'
        $colorsMap = [
            'perro' => '#FF6384', // Rojo para perros
            'gato' => '#36A2EB',  // Azul para gatos
            // Si hay otras especies en la BD, se les asignará el color por defecto
        ];

        // Inicializa contadores para Perro, Gato y Otros
        $perroCount = 0;
        $gatoCount = 0;
        $otherCount = 0;

        // 4. Procesa los resultados de la consulta
        foreach ($appointmentCounts as $item) {
            $species = strtolower($item->species); // Convierte la especie a minúsculas para una comparación consistente

            if ($species === 'perro') {
                $perroCount += $item->count;
            } elseif ($species === 'gato') {
                $gatoCount += $item->count;
            } else {
                $otherCount += $item->count; // Suma a 'Otro' si no es perro ni gato
            }
        }

        // 5. Rellena los arrays de datos y etiquetas solo con 'Perro', 'Gato' y 'Otro' si tienen datos
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
            $backgroundColors[] = '#CCCCCC'; // Color gris para 'Otro'
        }


        // Si no hay datos en absoluto para el periodo seleccionado, muestra un mensaje
        if (empty($data)) {
            $labels = ['No hay citas registradas para este periodo'];
            $data = [1]; // Un valor dummy para que el gráfico no esté completamente en blanco
            $backgroundColors = ['#DDDDDD']; // Color muy claro
        }

        // 6. Retorna los datos formateados para Chart.js
        return [
            'datasets' => [
                [
                    'label' => 'Número de Citas', // Leyenda del conjunto de datos
                    'data' => $data,             // Valores numéricos para cada segmento
                    'backgroundColor' => $backgroundColors, // Colores de los segmentos
                    'hoverOffset' => 4,          // Efecto visual al pasar el ratón por encima
                ],
            ],
            'labels' => $labels,                 // Etiquetas de los segmentos (ej. 'Perro', 'Gato')
        ];
    }

    /**
     * Opciones adicionales para personalizar la visualización del gráfico de pastel.
     * Utiliza las opciones de Chart.js.
     */
    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'position' => 'bottom', // Mueve la leyenda a la parte inferior del gráfico
                'labels' => [
                    'boxWidth' => 20,   // Ancho de la caja de color en la leyenda
                ]
            ],
        ],
        'responsive' => true,      // El gráfico se ajusta al tamaño de su contenedor
        'maintainAspectRatio' => false, // No fuerza una relación de aspecto fija
    ];
}