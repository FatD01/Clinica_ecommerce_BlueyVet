<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VeterinarianResource\Pages;
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
use Filament\Forms\Components\Select; // Importa este, es para el campo de selección
use App\Models\Specialty;
use Filament\Tables\Filters\SelectFilter;

class VeterinarianResource extends Resource
{
    protected static ?string $model = Veterinarian::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Gestión de Citas y Clínica';
    protected static ?int $navigationSort = 5; // Quinto


    protected static ?string $modelLabel = 'Veterinario';
    protected static ?string $pluralModelLabel = 'Veterinarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship(
                        'user',
                        'name',
                        fn(Builder $query) =>
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
                Select::make('specialties')
                    ->multiple() // Permite seleccionar varias especialidades para un mismo veterinario
                    ->relationship('specialties', 'name') // Define la relación: usa el método 'specialties' del modelo Veterinarian, y muestra el campo 'name' de Specialty
                    ->preload() // Precarga todas las especialidades disponibles, mejorando la experiencia de usuario
                    ->searchable() // Permite buscar especialidades dentro del selector
                    ->label('Especialidades del Veterinario') // Etiqueta visible en el formulario
                    ->placeholder('Selecciona una o más especialidades') // Texto indicativo
                    ->helperText('Asigna las especialidades pertinentes a este veterinario.'),
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
                Tables\Columns\TextColumn::make('specialties.name') // Accede a los nombres de las especialidades a través de la relación
                    ->badge() // Muestra cada especialidad como una "etiqueta" visualmente atractiva
                    ->wrap() // Si hay muchas especialidades, el texto se ajusta
                    ->searchable() // Permite buscar veterinarios por el nombre de sus especialidades
                    ->sortable() // Permite ordenar por especialidades (aunque puede ser menos útil en múltiples)
                    ->label('Especialidades Asignadas'), // Etiqueta para la columna en la tabla
            ])
            ->filters([
                TrashedFilter::make(),
                // Aquí puedes añadir un filter para la columna 'specialties.name' para buscar por nombre de especialidad
                SelectFilter::make('specialties') // El nombre del filtro puede ser el nombre de la relación
                ->relationship('specialties', 'name') // Conecta con la relación 'specialties' y muestra el 'name'
                ->multiple() // Permite seleccionar una o varias especialidades para filtrar
                ->preload() // Carga todas las opciones de especialidades al abrir el filtro
                ->searchable() // Permite buscar especialidades dentro del filtro
                ->label('Filtrar por Especialidad') // Etiqueta visible del filtro
                ->placeholder('Selecciona especialidades'),
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
