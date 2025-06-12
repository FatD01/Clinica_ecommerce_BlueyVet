<?php

namespace App\Filament\Resources\VeterinarianResource\RelationManagers;

use App\Models\VeterinarianException;
use App\Models\VeterinarianSchedule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification; // Importar Notification
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\ToggleButtons;

class ScheduleProposalsRelationManager extends RelationManager
{
    protected static string $relationship = 'scheduleProposals'; // Nombre de la relación en el modelo Veterinarian
    public static ?string $title = 'Propuestas de Horario del Veterinario'; // Título de la pestaña

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->label('Tipo de Propuesta')
                    ->options([
                        'recurring' => 'Horario Recurrente',
                        'exception' => 'Excepción de Horario',
                    ])
                    ->required()
                    ->reactive()
                    ->disabledOn('edit'), // No permitir cambiar el tipo en edición

                Grid::make(2)
                    ->schema([
                        DatePicker::make('date')
                            ->label('Fecha (Solo para Excepción)')
                            ->nullable()
                            ->hidden(fn (string $operation, \Filament\Forms\Get $get): bool => $get('type') !== 'exception')
                            ->requiredIf('type', 'exception')
                            ->minDate(now()),

                        Select::make('day_of_week')
                            ->label('Día de la Semana (Solo para Recurrente)')
                            ->nullable()
                            ->hidden(fn (string $operation, \Filament\Forms\Get $get): bool => $get('type') !== 'recurring')
                            ->requiredIf('type', 'recurring')
                            ->options([
                                0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                                4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
                            ]),
                    ]),

                Grid::make(2)
                    ->schema([
                        TimePicker::make('start_time')
                            ->label('Hora de Inicio')
                            ->required()
                            ->withoutSeconds(),

                        TimePicker::make('end_time')
                            ->label('Hora de Fin')
                            ->required()
                            ->after('start_time')
                            ->withoutSeconds(),
                    ]),

                Textarea::make('reason')
                    ->label('Motivo de la Propuesta')
                    ->nullable()
                    ->maxLength(500),

                ToggleButtons::make('status')
                    ->label('Estado de la Propuesta')
                    ->inline()
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ])
                    ->colors([
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    ])
                    ->default('pending')
                    ->disabled(fn (string $operation): bool => $operation === 'create'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason') // Título para el registro en el encabezado
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'recurring' => 'info',
                        'exception' => 'primary',
                    }),
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->placeholder('N/A'),
                TextColumn::make('day_name')
                    ->label('Día')
                    ->placeholder('N/A'),
                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i'),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->words(10)
                    ->tooltip(fn (string $state): ?string => $state)
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('Enviada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Select::make('status')
                    ->label('Filtrar por Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ]),
                Select::make('type')
                    ->label('Filtrar por Tipo')
                    ->options([
                        'recurring' => 'Horario Recurrente',
                        'exception' => 'Excepción',
                    ]),
            ])
            ->headerActions([
                // El administrador no crea propuestas aquí, las crean los veterinarios.
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (\App\Models\VeterinarianScheduleProposal $record) { // Asegúrate de importar el modelo
                        if ($record->status === 'approved') {
                            if ($record->type === 'recurring') {
                                VeterinarianSchedule::updateOrCreate(
                                    [
                                        'veterinarian_id' => $record->veterinarian_id,
                                        'day_of_week' => $record->day_of_week
                                    ],
                                    [
                                        'start_time' => $record->start_time,
                                        'end_time' => $record->end_time,
                                    ]
                                );
                                Notification::make()
                                    ->title('Horario recurrente aprobado y guardado.')
                                    ->success()
                                    ->send();
                            } elseif ($record->type === 'exception') {
                                VeterinarianException::updateOrCreate(
                                    [
                                        'veterinarian_id' => $record->veterinarian_id,
                                        'date' => $record->date
                                    ],
                                    [
                                        'start_time' => $record->start_time,
                                        'end_time' => $record->end_time,
                                        'type' => 'available', // Una propuesta de excepción aprobada es para disponibilidad
                                        'notes' => 'Aprobado: ' . $record->reason,
                                    ]
                                );
                                Notification::make()
                                    ->title('Excepción de horario aprobada y guardada.')
                                    ->success()
                                    ->send();
                            }
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function getEloquentQuery(): Builder
    {
        // Ordena las propuestas para que las pendientes aparezcan primero
        return $this->getRelationship()->getQuery()
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');
    }
}