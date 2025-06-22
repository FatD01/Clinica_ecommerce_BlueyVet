<?php
//archivo a eliminar tmb  esperen confirmacion
namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\ScheduleBlock;
use App\Models\BlockedDay;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;

class ScheduleCalendarWidget extends FullCalendarWidget
{
    protected static bool $shouldPersist = false;
    protected static ?string $heading = 'Calendario de Horarios';

    public function getConfig(): array
    {
        return [
            'initialView' => 'timeGridWeek',
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '20:00:00',
            'weekends' => false,
            'hiddenDays' => [0],
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'timeGridWeek,timeGridDay'
            ],
            'locale' => 'es',
            'selectable' => true,
            'editable' => true,
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
                'title' => $block->veterinarian->user->name ?? 'Veterinario',
                'start' => $block->start_time->toIso8601String(),
                'end'   => $block->end_time->toIso8601String(),
                'color' => '#' . substr(md5($block->veterinarian_id), 0, 6),
            ];
        }

        $blockedDays = BlockedDay::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();

        foreach ($blockedDays as $blockedDay) {
            $events[] = [
                'id' => 'blocked-day-' . $blockedDay->id,
                'title' => 'Día Bloqueado: ' . ($blockedDay->reason ?? 'Sin motivo'),
                'start' => $blockedDay->date->format('Y-m-d'),
                'end' => $blockedDay->date->copy()->addDay()->format('Y-m-d'),
                'allDay' => true,
                'display' => 'background',
                'color' => '#ffcccb',
            ];
        }

        return $events;
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('nombre')
                ->label('Nombre de prueba')
                ->required(),
        ];
    }

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->model(ScheduleBlock::class)
                ->mountUsing(function (Form $form, array $data) {
                    // No llenamos nada, solo activamos el formulario
                }),
            EditAction::make()
                ->model(ScheduleBlock::class),
            DeleteAction::make()
                ->model(ScheduleBlock::class),
        ];
    }
}
//VETE A DORMIR, YA VOY A CERRAR EL LIVESHARED | yAAAAA