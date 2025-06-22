<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\KeyValue; // Para payment_details

class OrderResource extends Resource
{
    // Define el modelo asociado a este recurso de Filament
    protected static ?string $model = Order::class;

    // Define el icono que aparecerá en la navegación del panel de Filament
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    // Define la etiqueta singular del recurso para la interfaz
    protected static ?string $singularLabel = 'Pedido';

    // Define la etiqueta plural del recurso para la interfaz
    protected static ?string $pluralLabel = 'Pedidos';

    // Define el orden de los elementos en la navegación lateral
    protected static ?int $navigationSort = 1;

    // Define el grupo de navegación, si tienes varios recursos relacionados
    protected static ?string $navigationGroup = 'Ventas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo para el usuario asociado al pedido.
                // Usamos Select para buscar usuarios y auto-completar.
                Select::make('user_id')
                    ->relationship('user', 'name') // Asume que tienes una relación 'user' en tu modelo Order y que el campo 'name' del modelo User es el que quieres mostrar.
                    ->searchable() // Permite buscar usuarios
                    ->preload() // Carga algunos usuarios inicialmente
                    ->nullable() // Permite que el campo sea nulo (para pedidos de invitados)
                    ->label('Usuario'),

                // ID de la orden de PayPal
                TextInput::make('paypal_order_id')
                    ->label('ID Orden PayPal')
                    ->unique(ignoreRecord: true) // Asegura que sea único al crear y actualizar
                    ->maxLength(255)
                    ->nullable(),

                // ID de la transacción de captura de PayPal
                TextInput::make('paypal_payment_id')
                    ->label('ID Pago PayPal')
                    ->maxLength(255)
                    ->nullable(),

                // Monto total del pedido
                TextInput::make('total_amount')
                    ->label('Monto Total')
                    ->numeric() // Solo permite números
                    ->inputMode('decimal') // Muestra un teclado decimal en dispositivos móviles
                    ->required() // Es un campo obligatorio
                    ->prefix('$') // Añade un prefijo de moneda
                    ->step(0.01), // Permite dos decimales

                // Moneda del pedido (PEN, USD, EUR, etc.)
                TextInput::make('currency')
                    ->label('Moneda')
                    ->required()
                    ->maxLength(3)
                    ->default('PEN'), // Valor por defecto

                // Estado del pedido
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false), // Para un estilo de selección más moderno en Filament

                // Detalles del pago (JSON de PayPal)
                // KeyValue es útil para almacenar datos JSON simples en pares clave-valor.
                // Si la estructura JSON de PayPal es muy compleja, podrías considerar un Textarea o un campo JSON más avanzado si lo necesitas.
                KeyValue::make('payment_details')
                    ->label('Detalles de Pago (JSON)')
                    ->nullable()
                    ->default([]) // Valor por defecto para evitar problemas si es nulo
                    ->keyLabel('Clave') // Etiqueta para la clave
                    ->valueLabel('Valor') // Etiqueta para el valor
                    ->addKeyButtonLabel('Añadir detalle'), // Etiqueta para el botón de añadir
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna para el ID del pedido
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                // Columna para el nombre del usuario asociado (si existe)
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->default('Invitado') // Muestra "Invitado" si user_id es nulo
                    ->sortable()
                    ->searchable(),

                // Columna para el ID de la orden de PayPal
                TextColumn::make('paypal_order_id')
                    ->label('ID Orden PayPal')
                    ->searchable(),

                // Columna para el monto total y la moneda
                TextColumn::make('total_amount')
                    ->label('Monto')
                    ->money(fn ($record) => $record->currency) // Formatea como moneda usando la columna 'currency'
                    ->sortable(),

                // Columna para el estado del pedido con insignias de colores
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'danger' => 'failed',
                        'info' => 'refunded',
                    ])
                    ->sortable(),

                // Columna para la fecha de creación
                TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime() // Formatea como fecha y hora
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto, pero visible al usuario

                // Columna para la fecha de última actualización
                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Columna para la fecha de eliminación suave
                TextColumn::make('deleted_at')
                    ->label('Fecha Eliminación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro para el estado del pedido
                SelectFilter::make('status')
                    ->label('Filtrar por Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                    ]),
                // Filtro para elementos eliminados suavemente
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Botón para ver detalles
                Tables\Actions\EditAction::make(), // Botón para editar
                Tables\Actions\DeleteAction::make(), // Botón para eliminar (soft delete)
                Tables\Actions\ForceDeleteAction::make(), // Botón para eliminar permanentemente
                Tables\Actions\RestoreAction::make(), // Botón para restaurar elementos eliminados
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Acción masiva para eliminar
                    Tables\Actions\ForceDeleteBulkAction::make(), // Acción masiva para eliminar permanentemente
                    Tables\Actions\RestoreBulkAction::make(), // Acción masiva para restaurar
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Puedes añadir Relation Managers aquí si tienes relaciones complejas (ej. OrderItemRelationManager)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            // 'view' => Pages\ViewOrder::route('/{record}'), // Página de vista detallada
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    // Define los atributos que pueden ser buscados globalmente en Filament
    public static function getGloballySearchableAttributes(): array
    {
        return ['paypal_order_id', 'total_amount', 'user.name'];
    }

    // Modifica la consulta base para incluir soft deletes
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}