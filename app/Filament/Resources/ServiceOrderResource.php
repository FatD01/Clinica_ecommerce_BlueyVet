<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\ServiceOrder;
use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Filament\Resources\ServiceOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter; // Importar Filter para tipado

// !!! IMPORTS NECESARIOS
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon; // Para manejo de fechas
use Illuminate\Support\Facades\Log; // ¡Mantén esta línea para depuración!
// FIN DE LOS IMPORTS

use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Gestión de Pedidos';
    protected static ?string $navigationLabel = 'Órdenes de Servicio';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Cliente (Usuario)')
                ->options(User::where('role', 'Cliente')->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->placeholder('Selecciona un cliente'),

            Select::make('service_id')
                ->relationship('service', 'name')
                ->label('Servicio')
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('amount')
                ->label('Monto Pagado (S/)')
                ->numeric()
                ->required()
                ->prefix('S/'),

            TextInput::make('paypal_order_id')
                ->label('ID de Orden PayPal')
                ->unique(ignoreRecord: true)
                ->nullable()
                ->maxLength(255),

            Select::make('status')
                ->label('Estado de Pago')
                ->options([
                    'pending' => 'Pendiente',
                    'completed' => 'Completado',
                    'failed' => 'Fallido',
                    'refunded' => 'Reembolsado',
                    'cancelled' => 'Cancelado',
                ])
                ->default('pending')
                ->required(),

            KeyValue::make('payment_details')
                ->label('Detalles del Pago (PayPal)')
                ->disabled()
                ->keyLabel('Clave')
                ->valueLabel('Valor')
                ->helperText('Contiene la respuesta JSON de PayPal. Este campo no es editable.')
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('user.name')->label('Cliente (Usuario)')->sortable()->searchable(),
                TextColumn::make('service.name')->label('Servicio')->sortable()->searchable(),
                TextColumn::make('amount')->label('Monto')->money('PEN')->sortable(),
                TextColumn::make('paypal_order_id')->label('ID PayPal')->searchable()->copyable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')->label('Fecha Creación')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Última Actualización')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('exportPdf')
                
                    ->label('Exportar PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Table $table) {
                        $query = $table->getLivewire()->getFilteredTableQuery();
                        $orders = $query->get();
                        $exportDate = Carbon::now()->format('d/m/Y H:i:s');

                        $activeFilters = [];

                        Log::info('--- Iniciando procesamiento de filtros para PDF ---');
                        // La siguiente línea causaba el error, la eliminamos.
                        // $allTableFiltersState = $table->getLivewire()->getTableFilters(); 
                        // Log::info('Estado inicial de todos los filtros de la tabla:', $allTableFiltersState);

                        foreach ($table->getFilters() as $filter) {
                            $filterName = $filter->getName();
                            $filterValue = $table->getLivewire()->getTableFilterState($filterName);

                            Log::info("Procesando filtro: '{$filterName}', Valor crudo: " . json_encode($filterValue));

                            // Saltamos los filtros que no tienen un valor efectivo
                            if (is_null($filterValue) || (is_string($filterValue) && strtolower($filterValue) === 'all')) {
                                Log::info("  Saltando filtro '{$filterName}': Valor nulo o 'all'.");
                                continue;
                            }
                            if (is_array($filterValue) && empty(array_filter($filterValue))) {
                                Log::info("  Saltando filtro '{$filterName}': Array vacío después de filtrar.");
                                continue;
                            }

                            $filterLabel = $filter->getLabel();
                            $readableValue = '';

                            // Manejo para SelectFilter (incluye 'status' y 'created_at_range')
                            if ($filter instanceof SelectFilter) {
                                $options = $filter->getOptions();
                                if (is_string($filterValue) && isset($options[$filterValue])) {
                                    $readableValue = $options[$filterValue];
                                    Log::info("  '{$filterName}' (SelectFilter) - Valor traducido: '{$readableValue}'");
                                } else if (is_array($filterValue)) {
                                     $translatedValues = [];
                                     foreach ($filterValue as $val) {
                                         $translatedValues[] = isset($options[$val]) ? $options[$val] : ucfirst(str_replace('_', ' ', (string) $val));
                                     }
                                     $readableValue = implode(', ', $translatedValues);
                                     Log::info("  '{$filterName}' (SelectFilter Multi) - Valor traducido: '{$readableValue}'");
                                } else {
                                    // Fallback para SelectFilter si el valor no es una clave de opción esperada
                                    $readableValue = ucfirst(str_replace('_', ' ', (string) $filterValue));
                                    Log::info("  '{$filterName}' (SelectFilter) - Fallback a ucfirst: '{$readableValue}'");
                                }
                            }
                            // Manejo para TrashedFilter
                            else if ($filter instanceof TrashedFilter) {
                                if ($filterValue === 'only_trashed') {
                                    $readableValue = 'Solo eliminados';
                                } else if ($filterValue === 'without_trashed') {
                                    $readableValue = 'Sin eliminados';
                                } else {
                                    $readableValue = 'Todos los registros';
                                }
                                Log::info("  '{$filterName}' (TrashedFilter) - Valor: '{$readableValue}'");
                            }
                            // Fallback general para otros tipos de filtros (ej. InputFilter de rango de fechas)
                            else {
                                if (is_array($filterValue)) {
                                    // Intenta manejar rangos de fecha si el array contiene 'start_date' y 'end_date'
                                    if (isset($filterValue['start_date']) && isset($filterValue['end_date'])) {
                                        try {
                                            $startDate = Carbon::parse($filterValue['start_date'])->format('d/m/Y');
                                            $endDate = Carbon::parse($filterValue['end_date'])->format('d/m/Y');
                                            $readableValue = $startDate . ' - ' . $endDate;
                                            Log::info("  '{$filterName}' (Date Range) - Valor: '{$readableValue}'");
                                        } catch (\Exception $e) {
                                            $readableValue = implode(', ', $filterValue); // Fallback si falla el parseo
                                            Log::warning("  Error parseando fechas para '{$filterName}'. Valor crudo: " . json_encode($filterValue));
                                        }
                                    } else {
                                        // Para otros arrays (ej. checkboxes múltiples si los hubiera)
                                        $readableValue = implode(', ', array_map(function($val) {
                                            return is_string($val) ? ucfirst(str_replace('_', ' ', $val)) : (string) $val;
                                        }, $filterValue));
                                        Log::info("  '{$filterName}' (Array General) - Valor: '{$readableValue}'");
                                    }
                                } else {
                                    // Para valores escalares (strings, numbers, booleans)
                                    $readableValue = ucfirst(str_replace('_', ' ', (string) $filterValue));
                                    Log::info("  '{$filterName}' (General Scalar) - Valor: '{$readableValue}'");
                                }
                            }

                            $activeFilters[] = [
                                'label' => $filterLabel,
                                'value' => $readableValue,
                            ];
                        }

                        Log::info('--- Filtros finales a enviar al Blade ---', $activeFilters);

                        $pdf = Pdf::loadView('PdfServicesOrders', [
                            'orders' => $orders,
                            'export_date' => $exportDate,
                            'active_filters' => $activeFilters,
                            'total_orders' => $orders->count(),
                            'total_amount' => $orders->sum('amount'),
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'ordenes_de_servicio_' . Carbon::now()->format('Ymd_His') . '.pdf');
                    })
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado de Pago')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                        'cancelled' => 'Cancelado',
                    ]),
                TrashedFilter::make(),
                SelectFilter::make('created_at_range')
                    ->label('Rango de Fecha de Creación')
                    ->options([
                        // 'all' => 'Todas las fechas',
                        'today' => 'Hoy',
                        'yesterday' => 'Ayer',
                        'last_7_days' => 'Últimos 7 días',
                        'current_month' => 'Mes actual',
                        'last_month_complete' => 'Último mes (completo)',
                        'current_year' => 'Año actual',
                        'last_year_complete' => 'Último año (completo)',
                    ])
                    // ->default('all')
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value']) || $data['value'] === 'all') {
                            return $query;
                        }

                        $now = Carbon::now();

                        return match ($data['value']) {
                            'today' => $query->whereDate('created_at', $now->toDateString()),
                            'yesterday' => $query->whereDate('created_at', $now->subDay()->toDateString()),
                            'last_7_days' => $query->where('created_at', '>=', $now->subDays(7)->startOfDay()),
                            'current_month' => $query
                                ->whereMonth('created_at', $now->month)
                                ->whereYear('created_at', $now->year),
                            'last_month_complete' => $query->whereBetween(
                                'created_at',
                                [Carbon::parse('first day of last month')->startOfDay(), Carbon::parse('last day of last month')->endOfDay()]
                            ),
                            'current_year' => $query->whereYear('created_at', $now->year),
                            'last_year_complete' => $query->whereBetween(
                                'created_at',
                                [Carbon::parse('first day of January ' . ($now->year - 1))->startOfDay(), Carbon::parse('last day of December ' . ($now->year - 1))->endOfDay()]
                            ),
                            default => $query,
                        };
                    })
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define RelationManagers si los necesitas
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceOrders::route('/'),
            'create' => Pages\CreateServiceOrder::route('/create'),
            'edit' => Pages\EditServiceOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}