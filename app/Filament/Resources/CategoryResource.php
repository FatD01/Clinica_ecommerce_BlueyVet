<?php
namespace App\Filament\Resources;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag'; // Opcional: Elige un icono
   protected static ?string $navigationGroup = 'Gestión de Inventario y Ventas ';
    protected static ?string $modelLabel = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de Categoría')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true), // Valida unicidad, ignorando el registro actual en edición
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->nullable(),
                
                // Campo para seleccionar la categoría padre
                Forms\Components\Select::make('parent_id')
                    ->label('Farmacia o Petshop?')
                    ->relationship('parent', 'name') // La relación 'parent' en el modelo Category
                    ->searchable()
                    ->preload()
                    ->nullable()
                    // Excluir la categoría actual de la lista de padres potenciales para evitar recursión infinita
                    ->options(function (?Category $record) {
                        return Category::when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                       ->pluck('name', 'id');
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_path') // Usamos el accessor que creamos
                    ->label('Ruta Completa')
                    ->searchable(['name', 'parent.name']) // Permite buscar por nombre propio o de padre
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ocultar por defecto
                Tables\Columns\TextColumn::make('parent.name') // Muestra el nombre de la categoría padre
                    ->label('Categoría Padre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ocultar por defecto
                Tables\Columns\TextColumn::make('products_count') // Muestra cuántos productos tiene esta categoría
                    ->counts('products') // Cuenta la relación 'products'
                    ->label('Productos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Filtrar por Categoría Padre')
                    ->options(Category::whereNull('parent_id')->pluck('name', 'id')), // Solo muestra categorías raíz como filtros
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Puedes añadir un RelationManager para ver los productos de una categoría o sus subcategorías
            // CategoryResource\RelationManagers\ProductsRelationManager::class,
            // CategoryResource\RelationManagers\ChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}