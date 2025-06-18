<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockedDayResource\Pages;
use App\Filament\Resources\BlockedDayResource\RelationManagers;
use App\Models\BlockedDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
class BlockedDayResource extends Resource
{
    protected static ?string $model = BlockedDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-x-circle'; // Un ícono que sugiera "bloqueado" o "prohibido"
    protected static ?string $navigationLabel = 'Días Bloqueados'; // Etiqueta en español para el menú
    protected static ?string $navigationGroup = 'Gestión de Horario de Veterinarios'; // Opcional: para agrupar en el menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Fecha Bloqueada')
                    ->native(false) // Usar el selector de Filament
                    ->unique(ignoreRecord: true) // La fecha debe ser única (un día solo se bloquea una vez)
                    ->required(),
                TextInput::make('reason')
                    ->label('Motivo del Bloqueo (Opcional)')
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }

     public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y') // Formato de fecha día/mes/año
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->placeholder('Sin motivo especificado'), // Si el motivo es nulo
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtros si los necesitas
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


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlockedDays::route('/'),
            'create' => Pages\CreateBlockedDay::route('/create'),
            'edit' => Pages\EditBlockedDay::route('/{record}/edit'),
        ];
    }
}
