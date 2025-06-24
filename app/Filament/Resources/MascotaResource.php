<?php

namespace App\Filament\Resources;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\MascotaResource\Pages;
use App\Filament\Resources\MascotaResource\RelationManagers;
use App\Models\Mascota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use App\Models\Cliente; // Asegúrate de importar el modelo Cliente
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class MascotaResource extends Resource
{
    protected static ?string $model = Mascota::class;

    protected static ?string $navigationGroup = 'Gestión de Citas y Clínica';
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Detalles de la Mascota')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('species')
                                    ->label('Especie')
                                    ->options([
                                        'perro' => 'Perro',
                                        'gato' => 'Gato',
                                    ])
                                    ->searchable(),
                                TextInput::make('race')
                                    ->label('Raza')
                                    ->maxLength(255),
                                TextInput::make('weight')
                                    ->label('Peso (kg)')
                                    ->numeric()
                                    ->step(0.1),
                                DatePicker::make('birth_date')
                                    ->label('Fecha de Nacimiento'),
                            ]),

                        Section::make('Información Adicional')
                            ->schema([
                                Textarea::make('allergies')
                                    ->label('Alergias')
                                    ->rows(2)
                                    ->maxLength(65535),
                                SpatieMediaLibraryFileUpload::make('avatar'),
                            ]),
                    ]),

                Section::make('Dueño')
                    ->schema([
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(Cliente::all()->pluck('nombre', 'id')->toArray()) // Carga los clientes existentes
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->label('Nombre del Cliente')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('apellido') // Añade el campo apellido si lo tienes
                                    ->label('Apellido del Cliente')
                                    ->maxLength(255),
                                // Agrega otros campos del cliente que quieras crear rápidamente
                            ])
                        // ->editOptionForm([
                        //     TextInput::make('nombre')
                        //         ->label('Nombre del Cliente')
                        //         ->required()
                        //         ->maxLength(255),
                        //     TextInput::make('apellido') // Añade el campo apellido si lo tienes
                        //         ->label('Apellido del Cliente')
                        //         ->maxLength(255),
                        // ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nombre') // Asegúrate de usar el campo correcto ('nombre' o 'name')
                    ->label('Dueño'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('species')
                    ->label('Especie')
                    ->searchable(),
                Tables\Columns\TextColumn::make('race')
                    ->label('Raza')
                    ->searchable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label('Peso (kg)'),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Fecha de Nacimiento'),


                SpatieMediaLibraryImageColumn::make('avatar'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                TrashedFilter::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMascotas::route('/'),
            'create' => Pages\CreateMascota::route('/create'),
            'edit' => Pages\EditMascota::route('/{record}/edit'),
        ];
    }
}
