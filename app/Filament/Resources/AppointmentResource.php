<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Filters\Filter; // Importar Filter para tipado en instanceof
use Illuminate\Support\Facades\Blade; // <-- NECESARIO PARA PDF
use Symfony\Component\HttpFoundation\StreamedResponse; // <-- NECESARIO PARA PDF
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Veterinarian;
use App\Models\Mascota;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;
    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
 protected static ?string $navigationGroup = 'Gestión de Citas y Clínica';
     protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Cita';
    protected static ?string $pluralModelLabel = 'Citas';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mascota_id')
                    ->relationship('mascota', 'name')
                    ->label('Mascota')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('veterinarian_id')
                    ->label('Veterinario')
                    ->relationship('veterinarian', 'id', function (Builder $query) {
                        $query->with('user');
                    })
                    ->getOptionLabelFromRecordUsing(function (\App\Models\Veterinarian $record) {
                        return $record->user?->name ?? 'N/A';
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DateTimePicker::make('date')
                    ->label('Fecha y Hora de la Cita')
                    ->required()
                    ->native(false)
                    ->seconds(false)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('end_datetime')
                    ->label('Hora final de la Cita')
                    ->required()
                    ->native(false)
                    ->seconds(false)
                    ->withoutDate() // Esto ocultará el selector de fecha y solo mostrará el de hora
                    ->default(function (Forms\Get $get) {
                        // Obtenemos la fecha del campo 'date'
                        $startDate = $get('date');

                        // Si 'date' tiene un valor, lo usamos. De lo contrario, usamos la fecha actual.
                        // Combinamos la fecha con una hora predeterminada (por ejemplo, 17:00:00)
                        return $startDate ? Carbon::parse($startDate)->setTime(17, 0, 0) : Carbon::now()->setTime(17, 0, 0);
                    })
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('reason')
                    ->label('Motivo')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'reprogrammed' => 'Reprogramada',
                    ])
                    ->label('Estado')
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mascota.name')
                    ->label('Mascota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('veterinarian.user.name')
                    ->label('Veterinario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha y Hora')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_datetime')
                    ->label('Hora de finalización')
                    ->Time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->limit(50)
                    // Esta es la corrección crucial que ya te di y mantendremos.
                    ->tooltip(fn(?string $state): ?string => (is_string($state) && strlen($state) > 50) ? $state : null),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'completed' => 'info', // Mantengo tu color original aquí
                        'cancelled' => 'danger',
                        'reprogrammed' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Fecha Creación')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Última Actualización')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('exportPdf')
                    ->label('Exportar PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (\Filament\Tables\Table $table) {
                        $livewire = $table->getLivewire();
                        $query = $livewire->getFilteredTableQuery();
                        $appointments = $query->with(['mascota', 'veterinarian.user'])->get();
                        $exportDate = Carbon::now()->format('d/m/Y H:i');

                        $activeFilters = [];

                        // ✅ Acceder a los filtros correctamente desde AppointmentResource
                        $filters = collect(AppointmentResource::table($table)->getFilters());

                        foreach ($filters as $filter) {
                            $filterName = $filter->getName();
                            $filterValue = $livewire->getTableFilterState($filterName);

                            if (is_null($filterValue) || (is_string($filterValue) && strtolower($filterValue) === 'all')) {
                                continue;
                            }

                            if (is_array($filterValue) && empty(array_filter($filterValue))) {
                                continue;
                            }

                            $filterLabel = $filter->getLabel();
                            $readableValue = '';

                            if ($filter instanceof \Filament\Tables\Filters\SelectFilter) {
                                $options = $filter->getOptions();



                                if (is_string($filterValue) && isset($options[$filterValue])) {
                                    $readableValue = $options[$filterValue];
                                } else if (is_array($filterValue)) {
                                    $translatedValues = [];
                                    foreach ($filterValue as $val) {
                                        $translatedValues[] = $options[$val] ?? ucfirst($val);
                                    }
                                    $readableValue = implode(', ', $translatedValues);
                                } else {
                                    $readableValue = ucfirst($filterValue);
                                }
                            } else {
                                $readableValue = is_array($filterValue)
                                    ? implode(', ', array_map('strval', $filterValue))
                                    : ucfirst($filterValue);
                            }

                            $activeFilters[] = [
                                'label' => $filterLabel,
                                'value' => $readableValue,
                            ];
                        }

                        $pdf = Pdf::loadView('PdfOrderProducts', [
                            'appointments' => $appointments,
                            'export_date' => $exportDate,
                            'active_filters' => $activeFilters,
                            'total_appointments' => $appointments->count(),
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'citas_' . Carbon::now()->format('Ymd_His') . '.pdf');
                    })
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Filtrar por Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'reprogrammed' => 'Reprogramada',
                    ]),
                SelectFilter::make('veterinarian_id')
                    ->relationship(
                        'veterinarian',
                        'id',
                        fn(Builder $query) => $query->join('users', 'veterinarians.user_id', '=', 'users.id')
                            ->select('veterinarians.id', 'users.name')
                            ->orderBy('users.name')
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                    ->label('Filtrar por Veterinario')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('created_at_range')
                    ->label('Rango de Fecha de Creación')
                    ->options([
                        'all' => 'Todas las fechas',
                        'today' => 'Hoy',
                        'yesterday' => 'Ayer',
                        'last_7_days' => 'Últimos 7 días',
                        'current_month' => 'Mes actual',
                        'last_month_complete' => 'Último mes (completo)',
                        'current_year' => 'Año actual',
                        'last_year_complete' => 'Último año (completo)',
                    ])
                    ->default('all')
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
                                [Carbon::now()->subMonth()->startOfMonth()->startOfDay(), Carbon::now()->subMonth()->endOfMonth()->endOfDay()]
                            ),
                            'current_year' => $query->whereYear('created_at', $now->year),
                            'last_year_complete' => $query->whereBetween(
                                'created_at',
                                [Carbon::now()->subYear()->startOfYear()->startOfDay(), Carbon::now()->subYear()->endOfYear()->endOfDay()]
                            ),
                            default => $query,
                        };
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
