<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Filament\Resources\ServiceOrderResource\RelationManagers;
use App\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // Importa Builder
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue; // Asegúrate de que KeyValue esté importado
use App\Models\User; // ¡IMPORTANTE: Importa el modelo User!

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Gestión de Pedidos';
    protected static ?string $navigationLabel = 'Órdenes de Servicio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Select::make('user_id')
                    ->label('Cliente (Usuario)')
                    ->options(
                        User::where('role', 'Cliente') // Filtra los usuarios donde la columna 'role' sea 'cliente'
                            ->pluck('name', 'id') // Muestra el 'name' del usuario y usa el 'id' como valor
                    )
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
                TextColumn::make('status')->label('Estado')->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'completed' => 'success',
                    'failed' => 'danger',
                    'refunded' => 'info',
                })->sortable(),
                TextColumn::make('created_at')->label('Fecha Creación')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Última Actualización')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado de Pago')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ])
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
                'index' => Pages\ListServiceOrders::route('/'),
                'create' => Pages\CreateServiceOrder::route('/create'),
                'edit' => Pages\EditServiceOrder::route('/{record}/edit'),
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