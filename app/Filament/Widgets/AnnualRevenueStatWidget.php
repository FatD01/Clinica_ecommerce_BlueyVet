<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use App\Models\OrderItem;
use App\Models\ServiceOrder;
use Filament\Support\Colors\Color;

class AnnualRevenueStatWidget extends BaseWidget
{

    protected const COLOR_PRODUCTOS_HEX = '#4BC0C0';
    protected const COLOR_SERVICIOS_HEX = '#FF6384';
    protected const COLOR_TOTAL_HEX = '#3B82F6';

    protected function getStats(): array
    {
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $totalProductos = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->selectRaw('SUM(order_items.quantity * order_items.price) as total')
            ->value('total') ?? 0;

        $totalServicios = ServiceOrder::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $total = $totalProductos + $totalServicios;

        return [
            Stat::make('Ganancia por Productos', 'S/ ' . number_format($totalProductos, 2))
                ->description('Últimos 12 meses')
                ->descriptionIcon('heroicon-m-cube')
                ->icon('heroicon-m-cube')
                ->color(Color::Cyan)
                ->extraAttributes([
                    'class' => 'bg-cyan-50 dark:bg-cyan-900/50 text-cyan-800 dark:text-cyan-300',
                ])
                ->chart([
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    (float) ($totalProductos > 0 ? ($totalProductos / 2) : 0.1),
                    (float) ($totalProductos > 0 ? $totalProductos : 0.1),
                ])
                ->chartColor(Color::Cyan),

            Stat::make('Ganancia por Servicios', 'S/ ' . number_format($totalServicios, 2))
                ->description('Últimos 12 meses')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->icon('heroicon-m-wrench-screwdriver')
                ->color(Color::Rose)
                ->extraAttributes([
                    'class' => 'bg-rose-50 dark:bg-rose-900/50 text-rose-800 dark:text-rose-300',
                ])
                ->chart([
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    (float) ($totalServicios > 0 ? ($totalServicios / 2) : 0.1),
                    (float) ($totalServicios > 0 ? $totalServicios : 0.1),
                ])
                ->chartColor(Color::Rose),

            Stat::make('Ganancia Total Anual', 'S/ ' . number_format($total, 2))
                ->description('Suma total (Productos + Servicios)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->icon('heroicon-m-banknotes')
                ->color(Color::Blue)
                ->extraAttributes([
                    'class' => 'bg-blue-50 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300',
                ])
                ->chart([
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    (float) ($total > 0 ? ($total / 2) : 0.1),
                    (float) ($total > 0 ? $total : 0.1),
                ])
                ->chartColor(Color::Blue),
        ];
    }
}
