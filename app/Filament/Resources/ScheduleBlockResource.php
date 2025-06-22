<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ScheduleBlockResource\Pages;
use App\Models\ScheduleBlock;
use App\Models\Veterinarian;
use App\Models\BlockedDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;

class ScheduleBlockResource extends Resource
{
    protected static ?string $model = ScheduleBlock::class;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('veterinarian_id')
                    ->label('Veterinario')
                    ->options(function () {
                        return Veterinarian::with('user')->get()->pluck('user.name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                DateTimePicker::make('start_time')
                    ->label('Fecha y Hora de Inicio')
                    ->native(false)
                    ->seconds(false)
                    ->required()
                    ->rules([
                        function (Forms\Get $get, Forms\Set $set, $state) {
                            $veterinarianId = $get('veterinarian_id');
                            $endTime = $get('end_time');
                            $recordId = $get('id');

                            if (!$veterinarianId || !$state || !$endTime) {
                                return;
                            }

                            $startTime = Carbon::parse($state);
                            $endTime = Carbon::parse($endTime);

                            if ($startTime->gte($endTime)) {
                                throw ValidationException::withMessages([
                                    'end_time' => 'La hora de fin debe ser posterior a la hora de inicio.',
                                ]);
                            }
                            $overlappingBlocks = ScheduleBlock::where('veterinarian_id', $veterinarianId)
                                ->where(function (Builder $query) use ($startTime, $endTime) {
                                    $query->whereBetween('start_time', [$startTime->copy()->addSecond(), $endTime->copy()->subSecond()])
                                          ->orWhereBetween('end_time', [$startTime->copy()->addSecond(), $endTime->copy()->subSecond()])
                                          ->orWhere(function (Builder $query) use ($startTime, $endTime) {
                                              $query->where('start_time', '>=', $startTime)
                                                    ->where('end_time', '<=', $endTime);
                                          })
                                          ->orWhere(function (Builder $query) use ($startTime, $endTime) {
                                              $query->where('start_time', '<', $startTime)
                                                    ->where('end_time', '>', $endTime);
                                          });
                                })
                                ->when($recordId, fn ($query) => $query->where('id', '!=', $recordId))
                                ->count();

                            if ($overlappingBlocks > 0) {
                                throw ValidationException::withMessages([
                                    'start_time' => 'Este horario se solapa con un turno existente para el mismo veterinario.',
                                ]);
                            }

                            if (BlockedDay::whereDate('date', $startTime->toDateString())->exists()) {
                                throw ValidationException::withMessages([
                                    'start_time' => 'No se pueden asignar horarios en días bloqueados (feriados o descansos generales).',
                                ]);
                            }
                        },
                    ])
                    ->columnSpan(1),

                DateTimePicker::make('end_time')
                    ->label('Fecha y Hora de Fin')
                    ->native(false)
                    ->seconds(false)
                    ->required()
                    ->afterOrEqual('start_time')
                    ->columnSpan(1),

                Toggle::make('is_recurring')
                    ->label('Repetir semanalmente')
                    ->helperText('Si se activa, se creará un bloque de horario recurrente semanalmente. (Lógica de creación de múltiples bloques se implementará más tarde.)')
                    ->default(false)
                    ->disabled()
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('veterinarian.user.name')
                    ->label('Veterinario')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('Recurrente')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListScheduleBlocks::route('/'),
            'create' => Pages\CreateScheduleBlock::route('/create'),
            'edit' => Pages\EditScheduleBlock::route('/{record}/edit'),
        ];
    }
}