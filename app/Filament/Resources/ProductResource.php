<?php
namespace App\Filament\Resources;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder; // Importa Builder para los filtros
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Filament\Tables\Actions\RestoreBulkAction;

use Filament\Forms\Components\TextInput; // Asegúrate de importar TextInput
use Filament\Forms\Components\Select; // Asegúrate de importar Select para el filtro
use Filament\Tables\Filters\SelectFilter; // Asegúrate de importar SelectFilter

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Gestión de Tienda';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Producto')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(4)
                    ->cols(20)
                    ->nullable(),
                Forms\Components\TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->prefix('S/.')
                    ->minValue(0.01),
                Forms\Components\TextInput::make('stock')
                    ->label('Stock Actual') // Cambiado a 'Stock Actual' para claridad
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0),

                // ==============================================
                // AÑADE ESTE CAMPO PARA EL UMBRAL MÍNIMO DE STOCK
                // ==============================================
                TextInput::make('min_stock_threshold')
                    ->label('Umbral Mínimo de Stock')
                    ->numeric()
                    ->integer() // Asegura que sea un número entero
                    ->minValue(0) // No puede ser negativo
                    ->required() // Hazlo requerido para que siempre haya un valor
                    ->default(10) // Valor por defecto, si la migración también tiene uno, deben coincidir
                    ->helperText('Cuando el stock caiga por debajo de este valor, se activará una notificación.'),

                // Campo para seleccionar la categoría
                Forms\Components\Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de Categoría')
                            ->required()
                            ->maxLength(100)
                            ->unique(Category::class, 'name'),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->nullable(),
                    ])
                    ->nullable(),

                Forms\Components\FileUpload::make('image')
                    ->label('Imagen del Producto')
                    ->image()
                    ->directory('product-images')
                    ->nullable()
                    ->visibility('public')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('PEN')
                    ->sortable(),

                // ==============================================
                // MODIFICA LA COLUMNA DE STOCK PARA MOSTRAR ALERTA VISUAL
                // ==============================================
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->color(function (Product $record): string {
                        // Si el stock actual es menor o igual al umbral, lo pinta de rojo
                        return $record->stock <= $record->min_stock_threshold ? 'danger' : 'success';
                    }),
                // ==============================================
                // AÑADE LA COLUMNA DEL UMBRAL MÍNIMO
                // ==============================================
                Tables\Columns\TextColumn::make('min_stock_threshold')
                    ->label('Umbral Mín.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Puedes ocultarlo por defecto si no quieres que siempre sea visible

                // Mostrar el nombre de la categoría relacionada
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),

                // Columna para mostrar la(s) promoción(es) activa(s)
                TextColumn::make('activePromotions')
                    ->label('Promoción(es) Activa(s)')
                    ->formatStateUsing(function (Product $record): string {
                        $activePromos = $record->promotions()
                            ->whereDate('start_date', '<=', Carbon::now())
                            ->whereDate('end_date', '>=', Carbon::now())
                            ->get();

                        if ($activePromos->isEmpty()) {
                            return 'Sin promociones activas';
                        }

                        $promoDetails = $activePromos->map(function (Promotion $promotion) {
                            return 'ID: ' . $promotion->id . ' - ' . $promotion->title;
                        })->implode('<br>');

                        return $promoDetails;
                    })
                    ->html()
                    ->wrap(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagen')
                    ->width(50)
                    ->height(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                // Filtro para promociones activas
                Tables\Filters\Filter::make('active_promotions')
                    ->label('Promociones Activas')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('promotions', function (Builder $query) {
                            $query->whereDate('start_date', '<=', Carbon::now())
                                  ->whereDate('end_date', '>=', Carbon::now());
                        });
                    }),
                // Filtro por categoría
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Filtrar por Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                // ==============================================
                // AÑADE ESTE NUEVO FILTRO PARA BAJO STOCK
                // ==============================================
                SelectFilter::make('stock_status')
                    ->label('Estado de Stock')
                    ->options([
                        'all' => 'Todos',
                        'low_stock' => 'Bajo Stock',
                        'in_stock' => 'En Stock',
                    ])
                    ->default('all') // Establece una opción por defecto
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'low_stock') {
                            // Usa el scope 'lowStock' que definimos en el modelo Product
                            $query->lowStock();
                        } elseif ($data['value'] === 'in_stock') {
                            // Filtra los que NO están en bajo stock
                            $query->whereColumn('stock', '>', 'min_stock_threshold');
                        }
                        return $query;
                    }),
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
                    // Si tienes ForceDeleteAction en tus acciones, también puedes tener ForceDeleteBulkAction aquí
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name', 'description',
        ];
    }
}