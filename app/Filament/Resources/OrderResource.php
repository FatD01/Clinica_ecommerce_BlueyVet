<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Gestión de Ventas';
    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Cliente')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('paypal_order_id')
                    ->label('ID de Orden PayPal')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('paypal_payment_id')
                    ->label('ID de Pago PayPal (Captura)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Monto Total')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->prefix('S/'),
                Forms\Components\TextInput::make('currency')
                    ->label('Moneda')
                    ->required()
                    ->maxLength(3)
                    ->default('PEN'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                        'cancelled' => 'Cancelado',
                        'processing' => 'Procesando',
                        'shipped' => 'Enviado',
                    ])
                    ->label('Estado')
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('payment_details')
                    ->label('Detalles de Pago (JSON)')
                    ->json()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('customer_address')
                    ->label('Dirección del Cliente')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paypal_order_id')
                    ->label('ID PayPal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Monto')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Moneda')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        'cancelled' => 'danger',
                        'processing' => 'primary',
                        'shipped' => 'secondary',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Fecha de Eliminación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filtrar por Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                        'cancelled' => 'Cancelado',
                        'processing' => 'Procesando',
                        'shipped' => 'Enviado',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Filtrar por Cliente')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    })
                    ->label('Fecha de Creación'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportPdf')
                    ->label('Exportar Órdenes (PDF)')
                    ->color('primary')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Table $table) {
                        $livewire = $table->getLivewire();

                        $query = $livewire->getFilteredTableQuery()
                            ->with(['user', 'items.product']);

                        $orders = $query->get();

                        $exportDate = Carbon::now('America/Lima')->format('d/m/Y H:i:s');
                        $activeFilters = [];

                        $searchTerm = $livewire->getTableSearch();
                        if (!empty($searchTerm)) {
                            $activeFilters[] = ['label' => 'Búsqueda Global', 'value' => $searchTerm];
                        }

                        $filamentFilters = $table->getFilters();
                        foreach ($filamentFilters as $filter) {
                            $filterName = $filter->getName();
                            $filterState = $livewire->getTableFilterState($filterName);

                            if (is_null($filterState) || (is_string($filterState) && strtolower($filterState) === 'all')) {
                                continue;
                            }
                            if (is_array($filterState) && empty(array_filter($filterState))) {
                                continue;
                            }
                            if ($filterName === 'trashed' && isset($filterState['value']) && $filterState['value'] === 'without_trashed') {
                                continue;
                            }

                            $filterLabel = $filter->getLabel();
                            $readableValue = '';

                            if ($filter instanceof Tables\Filters\SelectFilter) {
                                $options = $filter->getOptions();
                                if (isset($filterState['value']) && isset($options[$filterState['value']])) {
                                    $readableValue = $options[$filterState['value']];
                                } elseif (is_string($filterState) && isset($options[$filterState])) {
                                    $readableValue = $options[$filterState];
                                } elseif (is_array($filterState)) {
                                    $translatedValues = [];
                                    foreach ($filterState as $val) {
                                        $valueToUse = (is_array($val) && isset($val['value'])) ? $val['value'] : $val;
                                        $translatedValues[] = $options[$valueToUse] ?? ucfirst(str_replace('_', ' ', $valueToUse));
                                    }
                                    $readableValue = implode(', ', $translatedValues);
                                }
                            } elseif ($filter instanceof Tables\Filters\Filter && $filterName === 'created_at') {
                                $dateFrom = $filterState['created_from'] ?? null;
                                $dateUntil = $filterState['created_until'] ?? null;
                                if ($dateFrom && $dateUntil) {
                                    $readableValue = Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . Carbon::parse($dateUntil)->format('d/m/Y');
                                } elseif ($dateFrom) {
                                    $readableValue = 'Desde ' . Carbon::parse($dateFrom)->format('d/m/Y');
                                } elseif ($dateUntil) {
                                    $readableValue = 'Hasta ' . Carbon::parse($dateUntil)->format('d/m/Y');
                                }
                            } elseif ($filter instanceof Tables\Filters\TrashedFilter) {
                                if (isset($filterState['value'])) {
                                    switch ($filterState['value']) {
                                        case 'only_trashed':
                                            $readableValue = 'Solo Eliminados';
                                            break;
                                        case 'with_trashed':
                                            $readableValue = 'Incluir Eliminados';
                                            break;
                                        case 'without_trashed':
                                        default:
                                            continue 2;
                                    }
                                }
                            } else {
                                $valueToUse = (is_array($filterState) && isset($filterState['value'])) ? $filterState['value'] : $filterState;
                                $readableValue = ucfirst(str_replace('_', ' ', $valueToUse));
                            }

                            if (!empty($readableValue)) {
                                $activeFilters[] = [
                                    'label' => $filterLabel,
                                    'value' => $readableValue,
                                ];
                            }
                        }

                        $totalOrders = $orders->count();
                        $totalAmount = $orders->sum('total_amount');

                        $pdf = Pdf::loadView('pdf.PdfOrders', [
                            'orders' => $orders,
                            'export_date' => $exportDate,
                            'total_orders' => $totalOrders,
                            'total_amount' => $totalAmount,
                            'active_filters' => $activeFilters,
                        ]);

                        $filename = 'reporte_ordenes_' . Carbon::now('America/Lima')->format('Ymd_His') . '.pdf';

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, $filename);
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
