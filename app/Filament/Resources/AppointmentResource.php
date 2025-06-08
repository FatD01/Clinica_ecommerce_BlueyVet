<?php

namespace App\Filament\Resources;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment; // Asegúrate de que el modelo Appointment existe
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Cita'; // Agrega esto para una etiqueta más amigable
    protected static ?string $pluralModelLabel = 'Citas'; // Agrega esto

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mascota_id')
                    ->relationship('mascota', 'name')
                    ->label('Mascota')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('veterinarian_id')
                    ->label('Veterinario')
                    ->relationship('veterinarian', 'id', function (Builder $query) {
                        $query->with('user');
                    })
                    ->getOptionLabelFromRecordUsing(function (\App\Models\Veterinarian $record) {
                        // *** TEMPORALMENTE: Muestra AMBOS IDs para depuración ***
                        return "VET_ID: {$record->id} - USER_ID: {$record->user->id} - Nombre: {$record->user->name}";
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DateTimePicker::make('date')
                    ->label('Fecha y Hora de la Cita')
                    ->required()
                    ->native(false) // Esto puede mejorar la apariencia del selector de fecha/hora
                    ->seconds(false) // Opcional: si no quieres segundos
                    ->columnSpanFull(), // Para que ocupe todo el ancho
                Forms\Components\TextInput::make('reason')
                    ->label('Motivo')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->label('Estado')
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mascota.name')
                    ->label('Mascota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('veterinarian.user.name') // Para mostrar el nombre del veterinario asociado al usuario
                    ->label('Veterinario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha y Hora')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->limit(50), // Limita el texto en la tabla
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge() // Muestra el estado como una "etiqueta" con colores
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
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

                TrashedFilter::make(),
                // Puedes añadir filtros aquí, por ejemplo por estado o veterinario
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filtrar por Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ]),
                Tables\Filters\SelectFilter::make('veterinarian_id')
                    ->relationship(
                        'veterinarian',
                        'id',
                        fn(Builder $query) => $query->join('users', 'veterinarians.user_id', '=', 'users.id')
                            ->select('veterinarians.id', 'users.name')
                            ->orderBy('users.name')
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                    ->label('Filtrar por Veterinario')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Aquí puedes definir Relation Managers si los necesitas, por ejemplo, para ver historiales médicos de una cita
            // RelationManagers\MedicalRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'), // Esta línea define la ruta 'index'
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
