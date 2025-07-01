<?php

namespace App\Filament\Pages;

use App\Models\VeterinarianSchedule;
use App\Models\Veterinarian; 
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString; // Necesario para el TextColumn del color
use Filament\Forms\Components\Select; // Importa el componente Select

class Veterinarian_schedules extends Page implements HasTable, HasActions, HasForms
{
    use InteractsWithTable, InteractsWithActions, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.veterinarian_schedules';
    protected static ?string $navigationGroup = 'Gestión de Citas y Clínica';

    protected static ?string $modelLabel = 'Horario veterinario';
    protected static ?string $pluralModelLabel = 'Horarios veterinarios';
    protected static ?string $title = 'Gestión de Horarios de Veterinarios';

    public array $calendarEvents = [];

    // Define tus colores disponibles aquí
    const AVAILABLE_COLORS = [
        '#74BCEC' => 'Bluey Primario (Azul Claro)',
        '#E47C34' => 'Bluey Secundario (Naranja)',
        '#F2DC6D' => 'Bluey Amarillo Claro',
        '#F2C879' => 'Bluey Amarillo Dorado',
        '#393859' => 'Bluey Oscuro',
    ];

    public function mount(): void
    {
        $this->calendarEvents = $this->getCalendarEvents();
    }

    public function getCalendarEvents(): array
    {
        $daysOfWeek = [
            'domingo' => 0,
            'lunes' => 1,
            'martes' => 2,
            'miércoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            'sábado' => 6,
        ];

        return VeterinarianSchedule::with('veterinarian.user')->get()->map(function ($schedule) use ($daysOfWeek) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->veterinarian->user->name ?? 'Veterinario',
                'daysOfWeek' => [$daysOfWeek[$schedule->day_of_week] ?? 0],
                'startTime' => \Carbon\Carbon::parse($schedule->start_time)->format('H:i'),
                'endTime' => \Carbon\Carbon::parse($schedule->end_time)->format('H:i'),
                'startRecur' => '2020-01-01',
                'endRecur' => '2035-01-01',
                'display' => 'auto',
                'color' => $schedule->color, // FullCalendar usará este color
            ];
        })->toArray();
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return VeterinarianSchedule::query()->with(['veterinarian.user']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('veterinarian.user.name')
                    ->label('Veterinario')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('day_of_week')
                    ->label('Día')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i'),

                TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i'),

                // Mostrar el color como una bolita de color en la tabla
                TextColumn::make('color')
                    ->label('Color')
                    ->formatStateUsing(function (string $state): HtmlString {
                        // Opcional: mostrar el nombre legible del color si el valor es hexadecimal
                        $colorName = array_search($state, self::AVAILABLE_COLORS) ?: $state;
                        return new HtmlString(
                            '<div style="display: flex; align-items: center; gap: 8px;">' .
                                '<div style="width: 20px; height: 20px; background-color: ' . $state . '; border-radius: 50%; border: 1px solid #ccc;"></div>' .
                                '<span>' . $colorName . '</span>' .
                                '</div>'
                        );
                    }),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->actions([
                EditAction::make()
                    ->form(function (Form $form) {
                        return $form->schema([
                            Components\Select::make('veterinarian_id')
                                ->label('Veterinario')
                                ->relationship('veterinarian')
                                ->getOptionLabelFromRecordUsing(fn(Veterinarian $record) => $record->user->name ?? 'Sin nombre')
                                ->required()
                                ->searchable()
                                ->preload(),

                            Components\Select::make('day_of_week')
                                ->label('Día de la Semana')
                                ->options([
                                    'lunes' => 'Lunes',
                                    'martes' => 'Martes',
                                    'miércoles' => 'Miércoles',
                                    'jueves' => 'Jueves',
                                    'viernes' => 'Viernes',
                                    'sábado' => 'Sábado',
                                    'domingo' => 'Domingo',
                                ])
                                ->required(),

                            Components\TimePicker::make('start_time')
                                ->label('Hora de Inicio')
                                ->seconds(false)
                                ->required(),

                            Components\TimePicker::make('end_time')
                                ->label('Hora de Fin')
                                ->seconds(false)
                                ->required(),

                            // Usar Select para los colores predefinidos
                            Select::make('color') // Usar Select en lugar de ColorPicker
                                ->label('Color del Horario')
                                ->options(self::AVAILABLE_COLORS) // Cargar las opciones desde la constante
                                ->native(false) // Mejor apariencia de Filament
                                ->required()
                                ->default(array_key_first(self::AVAILABLE_COLORS)), // Establecer el primer color como por defecto
                        ]);
                    })
                    ->after(function (VeterinarianSchedule $record) {
                        $this->calendarEvents = $this->getCalendarEvents();
                        Notification::make()
                            ->title("Horario actualizado para: {$record->veterinarian->user->name}")
                            ->success()
                            ->send();
                        $this->js('window.location.reload()');
                    }),

                DeleteAction::make()
                    ->after(function () {
                        $this->calendarEvents = $this->getCalendarEvents();
                        $this->js('window.location.reload()');
                    }),
            ]);
    }

    public function createAction(): CreateAction
    {
        return CreateAction::make()
            ->label('Crear Nuevo Horario')
            ->model(VeterinarianSchedule::class)
            ->createAnother(false)
            ->form(function (Form $form) {
                return $form->schema([
                    Components\Select::make('veterinarian_id')
                        ->label('Veterinario')
                        ->relationship('veterinarian')
                        ->getOptionLabelFromRecordUsing(fn(Veterinarian $record) => $record->user->name ?? 'Sin nombre')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Components\Select::make('day_of_week')
                        ->label('Día de la Semana')
                        ->options([
                            'lunes' => 'Lunes',
                            'martes' => 'Martes',
                            'miércoles' => 'Miércoles',
                            'jueves' => 'Jueves',
                            'viernes' => 'Viernes',
                            'sábado' => 'Sábado',
                            'domingo' => 'Domingo',
                        ])
                        ->required(),

                    Components\TimePicker::make('start_time')
                        ->format('H:i')
                        ->label('Hora de Inicio')
                        ->seconds(false)
                        ->required(),

                    Components\TimePicker::make('end_time')
                        ->label('Hora de Fin')
                        ->seconds(false)
                        ->required(),

                    // Usar Select para los colores predefinidos
                    Select::make('color') // Usar Select en lugar de ColorPicker
                        ->label('Color del Horario')
                        ->options(self::AVAILABLE_COLORS) // Cargar las opciones desde la constante
                        ->native(false) // Mejor apariencia de Filament
                        ->required()
                        ->default(array_key_first(self::AVAILABLE_COLORS)), // Establecer el primer color como por defecto
                ]);
            })
            ->after(function (VeterinarianSchedule $record) {
                $this->calendarEvents = $this->getCalendarEvents();
                Notification::make()
                    ->title("Horario creado para: {$record->veterinarian->user->name}")
                    ->success()
                    ->send();
                $this->js('window.location.reload()');
            });
    }
}
