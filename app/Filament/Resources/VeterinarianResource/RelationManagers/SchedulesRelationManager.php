<?php

namespace App\Filament\Resources\VeterinarianResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules'; // Nombre de la relación en el modelo Veterinarian
    public static ?string $title = 'Horarios Recurrentes'; // Título de la pestaña

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('day_of_week')
                    ->label('Día de la Semana')
                    ->options([
                        0 => 'Domingo',
                        1 => 'Lunes',
                        2 => 'Martes',
                        3 => 'Miércoles',
                        4 => 'Jueves',
                        5 => 'Viernes',
                        6 => 'Sábado',
                    ])
                    ->required(),
                TimePicker::make('start_time')
                    ->label('Hora de Inicio')
                    ->required()
                    ->withoutSeconds(),
                TimePicker::make('end_time')
                    ->label('Hora de Fin')
                    ->required()
                    ->after('start_time')
                    ->withoutSeconds(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day_of_week')
            ->columns([
                TextColumn::make('day_name') // Usa el accessor del modelo VeterinarianSchedule
                    ->label('Día'),
                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}