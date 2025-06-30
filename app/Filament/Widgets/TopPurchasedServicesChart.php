<?php

namespace App\Filament\Widgets;

use App\Models\ServiceOrder;
use App\Models\Service;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TopPurchasedServicesChart extends ChartWidget
{
    protected static ?string $heading = 'Tendencias';
    protected static ?int $sort = 1;
    protected static string $color = 'primary';
    protected int | string | array $columnSpan = 'full';
    protected int | string | array $contentHeight = '340px';
    
    public ?string $filter = 'services'; // Ahora usamos el filtro clásico

    private array $customPalette = [
        '#4F46E5',
        '#EF4444',
        '#22C55E',
        '#FBBF24',
        '#8B5CF6',
        '#EC4899',
        '#06B6D4',
        '#F97316',
        '#A855F7',
        '#3B82F6',
    ];

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getFilters(): ?array
    {
        return [
            'services' => 'Servicios',
            'products' => 'Productos',
        ];
    }

    protected function getData(): array
    {
        $startDate = Carbon::now()->subMonth()->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $trendPeriod = 'perDay';

        $datasets = [];
        $labels = [];

        if ($this->filter === 'services') {
            $topServiceIds = ServiceOrder::query()
                ->select('service_id', DB::raw('count(*) as total_purchases'))
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('service_id')
                ->orderByDesc('total_purchases')
                ->limit(5)
                ->pluck('service_id');

            foreach ($topServiceIds as $index => $serviceId) {
                $serviceName = Service::find($serviceId)?->name ?? "Servicio (ID: {$serviceId})";

                $query = ServiceOrder::query()
                    ->where('service_id', $serviceId)
                    ->where('status', 'completed');

                $trend = Trend::query($query)
                    ->between($startDate, $endDate)
                    ->{$trendPeriod}()
                    ->count();

                $lineColor = $this->customPalette[$index % count($this->customPalette)];

                $datasets[] = [
                    'label' => $serviceName,
                    'data' => $trend->map(fn(TrendValue $value) => $value->aggregate)->toArray(),
                    'borderColor' => $lineColor,
                    'backgroundColor' => $lineColor . '40',
                    'fill' => 'origin',
                    'tension' => 0.4,
                    'pointRadius' => 5,
                    'pointBackgroundColor' => $lineColor,
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointHoverRadius' => 7,
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => $lineColor,
                    'pointHitRadius' => 10,
                ];

                if (empty($labels) && $trend->isNotEmpty()) {
                    $labels = $trend->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('M d'))->toArray();
                }
            }
        } elseif ($this->filter === 'products') {
            $topProductIds = OrderItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as total_quantity_purchased'))
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'completed')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->groupBy('product_id')
                ->orderByDesc('total_quantity_purchased')
                ->limit(5)
                ->pluck('product_id');

            foreach ($topProductIds as $index => $productId) {
                $productName = Product::find($productId)?->name ?? "Producto (ID: {$productId})";

                $query = OrderItem::query()
                    ->where('product_id', $productId)
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', 'completed');

                $trend = Trend::query($query)
                    ->dateColumn('orders.created_at') 
                    ->between($startDate, $endDate)
                    ->{$trendPeriod}()
                    ->sum('quantity');

                $lineColor = $this->customPalette[$index % count($this->customPalette)];

                $datasets[] = [
                    'label' => $productName,
                    'data' => $trend->map(fn(TrendValue $value) => $value->aggregate)->toArray(),
                    'borderColor' => $lineColor,
                    'backgroundColor' => $lineColor . '40',
                    'fill' => 'origin',
                    'tension' => 0.4,
                    'pointRadius' => 5,
                    'pointBackgroundColor' => $lineColor,
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointHoverRadius' => 7,
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => $lineColor,
                    'pointHitRadius' => 10,
                ];

                if (empty($labels) && $trend->isNotEmpty()) {
                    $labels = $trend->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('M d'))->toArray();
                }
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
                    'pointRadius' => 0,
                ]],
                'labels' => [''],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }
}
