<?php

namespace App\Filament\Pages;
use App\Filament\Widgets\TopPurchasedServicesChart;
use Filament\Pages\Dashboard as BaseDashboard; // Importa el Dashboard base de Filament

class Dashboard extends BaseDashboard
{
    // Puedes personalizar el ícono o título aquí si quieres
    // protected static ?string $navigationIcon = 'heroicon-o-home';
    // protected static ?string $title = 'Mi Panel de Control';
    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         // Estos son widgets por defecto, déjalos si los quieres
    //         // \App\Filament\Widgets\AccountWidget::class,
    //         // \App\Filament\Widgets\FilamentInfoWidget::class,

    //         // ¡Añade tu widget aquí!
    //         TopPurchasedServicesChart::class,
    //     ];
    // }
    // Por defecto, esta página ya sabe que es un dashboard y busca widgets.
    // No necesitas una propiedad $view = '...' a menos que quieras una vista Blade personalizada completa.
}