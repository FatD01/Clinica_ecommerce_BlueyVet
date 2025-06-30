<?php

namespace App\Filament\Resources;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select; // Para el campo de selección
use Filament\Forms\Components\FileUpload; // Importar FileUpload correctamente
use Filament\Tables\Filters\SelectFilter; // Importar SelectFilter correctamente
use Filament\Tables\Columns\ImageColumn; // Importar ImageColumn correctamente
use App\Models\Specialty;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Gestión de Citas y Clínica';
    protected static ?string $pluralLabel = 'Servicios';
    protected static ?string $singularLabel = 'Servicio';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('S/.'),
                Forms\Components\TextInput::make('duration_minutes')
                    ->numeric()
                    ->default(null),

                Forms\Components\Select::make('status')
                    ->label('Estado del Servicio')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ])
                    ->default('active')
                    ->required(),

                // Campo de selección múltiple para las especialidades
                Select::make('specialties')
                    ->multiple() // Permite seleccionar varias especialidades para un mismo servicio
                    ->relationship('specialties', 'name') // Conecta con la relación 'specialties' y muestra el 'name'
                    ->preload() // Precarga todas las especialidades disponibles
                    ->searchable() // Permite buscar especialidades dentro del selector
                    ->label('Especialidades Requeridas') // Etiqueta visible en el formulario
                    ->placeholder('Selecciona una o más especialidades')
                    ->helperText('Las especialidades que un veterinario debe tener para ofrecer este servicio.'),

                // Opcional: un toggle para el estado inicial de "disponible" si no quieres que sea solo dinámico
                // Forms\Components\Toggle::make('is_available')
                //     ->label('Servicio Disponible Inmediatamente')
                //     ->default(false) // Por defecto, podría ser "pronto disponible"
                //     ->helperText('Activa esto solo si sabes que hay veterinarios con las especialidades requeridas.'),
                // FileUpload::make('image_url')
                //     ->label('Imagen del Servicio')
                //     ->image() // Valida que el archivo sea una imagen
                //     ->directory('services-images') // Directorio dentro de storage/app/public (o lo que configures)
                //     ->visibility('public') // Hace que la imagen sea accesible públicamente
                //     ->disk('public'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // ImageColumn::make('image_url')
                //     ->label('Imagen')
                //     ->square() // Hace la imagen cuadrada en la tabla
                //     ->width(50)
                //     ->height(50),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('specialties.name') // Muestra los nombres de las especialidades
                    ->badge()
                    ->wrap()
                    ->searchable()
                    ->label('Especialidades Requeridas'),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('specialties')
                    ->relationship('specialties', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Filtrar por Especialidad Requerida'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
