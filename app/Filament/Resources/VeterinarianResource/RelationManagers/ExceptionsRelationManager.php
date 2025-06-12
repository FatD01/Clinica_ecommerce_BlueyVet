<?php

namespace App\Filament\Resources\VeterinarianResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExceptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'exceptions'; // Nombre de la relación en el modelo Veterinarian
    public static ?string $title = 'Excepciones de Horario'; // Título de la pestaña

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Fecha de la Excepción')
                    ->required()
                    ->minDate(now()),
                Select::make('type')
                    ->label('Tipo de Excepción')
                    ->options([
                        'unavailable' => 'No Disponible (Cerrado, Vacaciones, etc.)',
                        'available' => 'Disponible (Horario extra, turnos especiales)',
                    ])
                    ->required(),
                TimePicker::make('start_time')
                    ->label('Hora de Inicio (opcional para "No Disponible")')
                    ->nullable()
                    ->withoutSeconds(),
                TimePicker::make('end_time')
                    ->label('Hora de Fin (opcional para "No Disponible")')
                    ->nullable()
                    ->afterOrEqual('start_time')
                    ->withoutSeconds(),
                Textarea::make('notes')
                    ->label('Notas/Motivo')
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date') // Usa la fecha como título para el registro
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'unavailable' => 'danger',
                    }),
                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i')
                    ->placeholder('Todo el día'),
                TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i')
                    ->placeholder('Todo el día'),
                TextColumn::make('notes')
                    ->label('Notas')
                    ->words(10)
                    ->tooltip(fn (string $state): ?string => $state)
                    ->wrap(),
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