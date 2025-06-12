<?php
namespace App\Filament\Resources;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Category; // Asegúrate de este 'use'
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Filament\Tables\Actions\RestoreBulkAction;

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
                    ->label('Stock')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0),

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
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),

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