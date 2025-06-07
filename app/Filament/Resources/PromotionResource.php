<?php
namespace App\Filament\Resources;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use App\Models\Product; // Asegúrate de importar el modelo Product
use App\Models\Service; // Importa el modelo Service si lo tienes
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Tables\Actions\RestoreBulkAction;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Gestión de Tienda';
    protected static ?string $modelLabel = 'Promoción';
    protected static ?string $pluralModelLabel = 'Promociones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título de la Promoción')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3)
                    ->cols(20)
                    ->nullable(),

                // Nuevo campo: A qué aplica la promoción (Producto o Servicio)
                Forms\Components\Select::make('apply_to')
                    ->label('Aplica a')
                    ->options([
                        'product' => 'Producto(s)',
                        'service' => 'Servicio(s)',
                    ])
                    ->required()
                    ->live() // Hace que este campo sea reactivo
                    ->afterStateUpdated(function (Forms\Set $set) {
                        // Limpiar campos de selección de productos/servicios al cambiar 'apply_to'
                        $set('products', []); // Si usas products()
                        $set('services', []); // Si usas services()
                    }),

                // Selector de productos (visible si 'apply_to' es 'product')
                Forms\Components\Select::make('products')
                    ->label('Seleccionar Producto(s)')
                    ->multiple()
                    ->relationship('products', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Forms\Get $get) => $get('apply_to') === 'product'),

                // Selector de servicios (visible si 'apply_to' es 'service')
                Forms\Components\Select::make('services')
                    ->label('Seleccionar Servicio(s)')
                    ->multiple()
                    ->relationship('services', 'name') // Asumiendo que tienes un modelo Service y una relación
                    ->searchable()
                    ->preload()
                    ->visible(fn (Forms\Get $get) => $get('apply_to') === 'service'),

                Forms\Components\Toggle::make('is_enabled')
                    ->label('Habilitar Promoción')
                    ->hint('Activa o desactiva esta promoción manualmente.')
                    ->inline(false) // Para que el switch se muestre solo
                    ->default(true),

                // Lógica de Descuento
                Forms\Components\Select::make('discount_type')
                    ->label('Tipo de Descuento')
                    ->options([
                        'none' => 'Sin Descuento (Solo para mostrar información)',
                        'percentage' => 'Porcentaje de Descuento',
                        'fixed_amount' => 'Cantidad Fija de Descuento',
                        'buy_x_get_y' => 'Compra X, Lleva Y Gratis',
                    ])
                    ->required()
                    ->live() // Hace que este campo sea reactivo
                    ->default('none'), // Por defecto, sin descuento

                // Campos condicionales según el tipo de descuento
                Forms\Components\TextInput::make('discount_value')
                    ->label('Valor del Descuento')
                    ->numeric()
                    ->minValue(0)
                    ->suffix(fn (Forms\Get $get): string => $get('discount_type') === 'percentage' ? '%' : ' S/.')
                    ->required()
                    ->visible(fn (Forms\Get $get): bool => in_array($get('discount_type'), ['percentage', 'fixed_amount'])),

                Forms\Components\Grid::make(2) // Usar un Grid para alinear X e Y
                    ->visible(fn (Forms\Get $get): bool => $get('discount_type') === 'buy_x_get_y')
                    ->schema([
                        Forms\Components\TextInput::make('buy_quantity')
                            ->label('Cantidad a Comprar (X)')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(),
                        Forms\Components\TextInput::make('get_quantity')
                            ->label('Cantidad Gratis (Y)')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(),
                    ]),

                // Fechas de inicio y fin
                Forms\Components\DatePicker::make('start_date')
                    ->label('Fecha de Inicio')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Fecha de Fin')
                    ->required()
                    ->minDate(fn (Forms\Get $get) => $get('start_date') ?: now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('apply_to')
                    ->label('Aplica a')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'product' => 'Producto(s)',
                        'service' => 'Servicio(s)',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('discount_info')
                    ->label('Descuento')
                    ->state(function (Promotion $record): string {
                        switch ($record->discount_type) {
                            case 'percentage':
                                return $record->discount_value . '% OFF';
                            case 'fixed_amount':
                                return $record->discount_value . ' S/. OFF';
                            case 'buy_x_get_y':
                                return 'Compra ' . $record->buy_quantity . ', Lleva ' . $record->get_quantity . ' Gratis';
                            default:
                                return 'No Aplica';
                        }
                    })
                    ->color(fn (string $state): string => $state === 'No Aplica' ? 'secondary' : 'success'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Habilitada')
                    ->boolean(),
                Tables\Columns\TextColumn::make('current_status')
                    ->label('Estado Actual')
                    ->state(function (Promotion $record): string {
                        if (!$record->is_enabled) {
                            return 'Deshabilitada';
                        }
                        $now = Carbon::now();
                        if ($now->between($record->start_date, $record->end_date)) {
                            return 'Activa';
                        } elseif ($now->lt($record->start_date)) {
                            return 'Próxima';
                        } else {
                            return 'Finalizada';
                        }
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'Activa' => 'success',
                            'Próxima' => 'info',
                            'Finalizada' => 'danger',
                            'Deshabilitada' => 'gray',
                            default => 'secondary',
                        };
                    })
                    ->badge(),
                // Columna para listar los productos asociados
                TextColumn::make('products.name')
                    ->label('Productos Asociados')
                    ->listWithLineBreaks()
                    ->wrap()
                    ->limit(50),
                // Columna para listar los servicios asociados (si los tienes)
                TextColumn::make('services.name')
                    ->label('Servicios Asociados')
                    ->listWithLineBreaks()
                    ->wrap()
                    ->limit(50),
            ])
            ->filters([


                TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('apply_to')
                    ->label('Aplica a')
                    ->options([
                        'product' => 'Producto(s)',
                        'service' => 'Servicio(s)',
                    ]),
                Tables\Filters\SelectFilter::make('discount_type')
                    ->label('Tipo de Descuento')
                    ->options([
                        'none' => 'Sin Descuento',
                        'percentage' => 'Porcentaje',
                        'fixed_amount' => 'Cantidad Fija',
                        'buy_x_get_y' => 'Compra X, Lleva Y',
                    ]),
                Tables\Filters\Filter::make('is_active')
                    ->label('Promociones Activas Ahora')
                    ->query(fn (Builder $query) => $query->where('is_enabled', true)->whereDate('start_date', '<=', Carbon::now())->whereDate('end_date', '>=', Carbon::now())),
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Habilitada')
                    ->trueLabel('Habilitada')
                    ->falseLabel('Deshabilitada')
                    ->nullable(),
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
            // Puedes añadir RelationManagers si quieres gestionar productos/servicios desde la página de edición de la promoción
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}