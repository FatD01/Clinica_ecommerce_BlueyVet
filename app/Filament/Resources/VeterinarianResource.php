<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VeterinarianResource\Pages;
use App\Filament\Resources\VeterinarianResource\RelationManagers;
use App\Models\Veterinarian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User; // Import the User model
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\RelationManagers\SchedulesRelationManager;

class VeterinarianResource extends Resource
{
    protected static ?string $model = Veterinarian::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $modelLabel = 'Veterinario';
    protected static ?string $pluralModelLabel = 'Veterinarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name', fn (Builder $query) =>
                        $query->whereDoesntHave('veterinarian') // Excluye usuarios que ya son veterinarios
                              ->where('role', '!=', 'admin') 
                              ->where('role', '!=', 'cliente') //oe creo que se como hacerlo| lte complicaste oye jjasjdjas la iaaaa|    pero creo qeu ya estaría tod, deja pruebo    ee| que quieres hacer? que solo aparezcan usuarios con rol veterinario qeu aun no hayan sido asignados coomo veterinario, qeu no aparezcan admin ni  lcientes
                    )
                    ->label('Usuario Asociado')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('license_number')
                    ->label('Número de Licencia')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('specialty')
                    ->label('Especialidad')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección')
                    ->maxLength(255),
                Forms\Components\Textarea::make('bio')
                    ->label('Biografía')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->label('Licencia')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialty')
                    ->label('Especialidad')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Dirección')
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
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
            // Aquí puedes añadir un RelationManager para MedicalRecords si un veterinario tiene muchos historiales
            // RelationManagers\MedicalRecordsRelationManager::class,
            // RelationManagers\SchedulesRelationManager::class,
            // RelationManagers\ExceptionsRelationManager::class,
            // RelationManagers\ScheduleProposalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVeterinarians::route('/'),
            'create' => Pages\CreateVeterinarian::route('/create'),
            'edit' => Pages\EditVeterinarian::route('/{record}/edit'),
        ];
    }
}