<?php

namespace App\Filament\Widgets;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\ScheduleBlock;
use App\Models\BlockedDay;
use Carbon\Carbon;
use Filament\Forms\Form; // Asegúrate de que esta importación esté presente si usas Form
use App\Filament\Resources\ScheduleBlockResource;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;

class ScheduleCalendarWidget extends FullCalendarWidget
{
    protected static bool $shouldPersist = false;
    protected static ?string $heading = 'Calendario de Horarios';

    // Este método ya no se necesita si usamos la directiva x-filament-fullcalendar::calendar en la vista,
    // ya que las acciones se pasan directamente como prop.
    // protected function getHeaderActions(): array
    // {
    //     return [];
    // }

    public function getConfig(): array
    {
        return [
            'initialView' => 'timeGridWeek',
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '20:00:00',
            'weekends' => false,
            'hiddenDays' => [0], // 0 = domingo (Sunday)
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'timeGridWeek,timeGridDay'
            ],
            'locale' => 'es',
            'slotLabelFormat' => [
                'hour' => 'numeric',
                'minute' => '2-digit',
                'omitZeroMinute' => false,
                'meridiem' => 'short',
                'hour12' => true
            ],
            'dayHeaderFormat' => [
                'weekday' => 'short',
                'day' => '2-digit',
                'month' => '2-digit',
                'omitCommas' => true
            ],
            'selectable' => true, // ¡IMPORTANTE!
            'editable' => true,   // ¡IMPORTANTE!
            // ELIMINADAS: 'selectHelper' y 'schedulerLicenseKey'
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $events = [];
        $start = Carbon::parse($fetchInfo['start']);
        $end = Carbon::parse($fetchInfo['end']);

        $scheduleBlocks = ScheduleBlock::with('veterinarian.user')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_time', [$start, $end])
                      ->orWhereBetween('end_time', [$start, $end]);
            })
            ->get();

        foreach ($scheduleBlocks as $block) {
            $events[] = [
                'id'    => $block->id,
                'title' => $block->veterinarian->user->name ?? 'Veterinario Desconocido',
                'start' => $block->start_time->toIso8601String(),
                'end'   => $block->end_time->toIso8601String(),
                'color' => '#' . substr(md5($block->veterinarian_id), 0, 6), // ¡CORREGIDO!
                // ELIMINADO: 'resourceId'
            ];
        }

        $blockedDays = BlockedDay::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();

        foreach ($blockedDays as $blockedDay) {
            $events[] = [
                'id' => 'blocked-day-' . $blockedDay->id,
                'title' => 'Día Bloqueado: ' . ($blockedDay->reason ?? 'Sin motivo'),
                'start' => $blockedDay->date->format('Y-m-d'),
                'end' => $blockedDay->date->addDay()->format('Y-m-d'),
                'allDay' => true,
                'display' => 'background',
                'color' => '#ffcccb',
            ];
        }

        return $events;
    }

    public function getFormSchema(): array
    {
        return ScheduleBlockResource::form(new Form($this))->getComponents();
    }

    // Este es el método que pasa las acciones al calendario.
    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->model(ScheduleBlock::class)
                ->resource(ScheduleBlockResource::class)
                ->mountUsing(function (Form $form, array $data) {
                    // dd('CreateAction mountUsing ha sido llamado!', $data); // Solo para depurar
                    if (isset($data['start'])) {
                        $form->fill([
                            'start_time' => Carbon::parse($data['start']),
                            'end_time' => Carbon::parse($data['end']),
                        ]);
                    }
                }),
            EditAction::make()
                ->model(ScheduleBlock::class)
                ->resource(ScheduleBlockResource::class),
            DeleteAction::make()
                ->model(ScheduleBlock::class),
        ];
    }
}