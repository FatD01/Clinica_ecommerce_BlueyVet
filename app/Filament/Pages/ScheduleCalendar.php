<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\ScheduleCalendarWidget; // Importa tu widget

class ScheduleCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Horarios Administrativos';
    protected static ?string $title = 'Calendario de Horarios del Personal';
    protected static ?string $slug = 'horarios-admin';

    protected static string $view = 'filament.pages.schedule-calendar';

    // ¡CRÍTICO! Esto le dice a Filament que incluya tu widget en esta página.
    protected function getWidgets(): array
    {
        return [
            ScheduleCalendarWidget::class,
        ];
    }

    // Opcional: Si quieres que el widget aparezca en una sección específica del encabezado.
    protected function getHeaderWidgets(): array
    {
        return [
            ScheduleCalendarWidget::class,
        ];
    }
}