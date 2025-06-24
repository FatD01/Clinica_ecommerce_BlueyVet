<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecialtyResource\Pages;
use App\Filament\Resources\SpecialtyResource\RelationManagers;
use App\Models\Specialty;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; // Importa esto si usas soft deletes
use Illuminate\Database\Eloquent\SoftDeletes; 

class SpecialtyResource extends Resource
{
    protected static ?string $model = Specialty::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag'; // Icono para la navegación
        protected static ?string $navigationGroup = 'Gestión de Citas y Clínica';
    protected static ?string $navigationLabel = 'Especialidades'; // Etiqueta en el menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true) // Asegura que el nombre sea único, ignorando el registro actual al editar
                    ->columnSpanFull() // Ocupa todo el ancho si estás en un formulario con columnas
                    ->label('Nombre de la Especialidad'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->nullable()
                    ->columnSpanFull()
                    ->label('Descripción'),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true)
                    ->label('Activa'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('description')
                    ->toggleable(isToggledHiddenByDefault: true) // Oculta por defecto, se puede mostrar
                    ->limit(50) // Limita la descripción para la vista de tabla
                    ->label('Descripción'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Activa'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creada el'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Actualizada el'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active') // Filtro para especialidades activas/inactivas
                    ->label('Estado')
                    ->trueLabel('Activas')
                    ->falseLabel('Inactivas'),
                Tables\Filters\TrashedFilter::make(), // Filtro para SoftDeletes
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Para borrado lógico
                Tables\Actions\ForceDeleteAction::make(), // Para borrado físico
                Tables\Actions\RestoreAction::make(), // Para restaurar borrados lógicos
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListSpecialties::route('/'),
            'create' => Pages\CreateSpecialty::route('/create'),
            'edit' => Pages\EditSpecialty::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}