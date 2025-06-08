<?php

namespace App\Filament\Resources;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction; // Para eliminar permanentemente si es necesario
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\MedicalRecordResource\Pages;
use App\Models\MedicalRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Historial Médico';
    protected static ?string $pluralModelLabel = 'Historiales Médicos';

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

                // Forms\Components\Select::make('veterinarian_id')
                //     ->label('Veterinario')
                //     ->options(
                //         \App\Models\User::where('role', 'veterinario')
                //             ->pluck('name', 'id')
                //     )
                //     ->searchable()
                //     ->preload()
                //     ->required()
                //     ->helperText('Selecciona el veterinario que atendió la consulta (solo se muestran cuentas de veterinario).'),

                Forms\Components\Select::make('veterinarian_id')
                    ->label('Veterinario')
                    ->required()
                    ->searchable()
                    ->options(function () {
                        return \App\Models\Veterinarian::query()
                            ->join('users', 'veterinarians.user_id', '=', 'users.id')
                            ->where('users.role', 'Veterinario')
                            ->pluck('users.name', 'veterinarians.id');
                    })
                    ->getSearchResultsUsing(function (string $searchQuery) {
                        return \App\Models\Veterinarian::query()
                            ->join('users', 'veterinarians.user_id', '=', 'users.id')
                            ->where('users.role', 'Veterinario')
                            ->where('users.name', 'like', "%{$searchQuery}%")
                            ->pluck('users.name', 'veterinarians.id')
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn($value): ?string => \App\Models\Veterinarian::find($value)?->user?->name ?? 'N/A')
                    ->preload(),

                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->label('Servicio Realizado')
                    ->nullable()
                    ->searchable()
                    ->preload(),

                Forms\Components\DatePicker::make('consultation_date')
                    ->label('Fecha de Consulta')
                    ->required(),
                Forms\Components\TextInput::make('reason_for_consultation')
                    ->label('Motivo de Consulta')
                    ->maxLength(255),
                Forms\Components\Textarea::make('diagnosis')
                    ->label('Diagnóstico')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('treatment')
                    ->label('Tratamiento')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('prescription')
                    ->label('Receta')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('observations')
                    ->label('Observaciones')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('notes')
                    ->label('Notas Adicionales')
                    ->maxLength(255),

                Forms\Components\Select::make('appointment_id')
                    // Cambia 'date' por 'id' si solo quieres mostrar el ID de la cita
                    // o cualquier otra columna que siempre tenga un valor de cadena
                    ->relationship('appointment', 'id') // O cualquier otra columna que no sea nula
                    ->getOptionLabelFromRecordUsing(fn(\App\Models\Appointment $record) => 'Cita #' . $record->id . ' - ' . ($record->date?->format('Y-m-d H:i A') ?? 'Fecha no disponible'))
                    ->label('Cita Asociada')
                    ->nullable()
                    ->searchable()  
                    ->preload(),

                Forms\Components\FileUpload::make('pfd_file')
                    ->label('Archivo PDF Adjunto')
                    ->directory('medical_records_pdfs')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['mascota', 'veterinarian.user', 'service', 'appointment']))
            ->columns([
                Tables\Columns\TextColumn::make('mascota.name')
                    ->label('Mascota')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ?? 'N/A'),

                Tables\Columns\TextColumn::make('veterinarian.user.name')
                    ->label('Veterinario')
                    ->formatStateUsing(fn($record) => $record->veterinarian?->user?->name ?? 'N/A')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('veterinarian.user', function (Builder $userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->join('veterinarians', 'medical_records.veterinarian_id', '=', 'veterinarians.id')
                            ->join('users', 'veterinarians.user_id', '=', 'users.id')
                            ->orderBy('users.name', $direction);
                    }),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ?? 'N/A'),

                Tables\Columns\TextColumn::make('consultation_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason_for_consultation')
                    ->label('Motivo')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnóstico')
                    ->limit(50),
                Tables\Columns\TextColumn::make('prescription')
                    ->label('Receta')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('observations')
                    ->label('Observaciones')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pfd_file')
                    ->label('PDF')
                    ->formatStateUsing(fn($state) => $state ? 'Ver PDF' : 'N/A')
                    ->url(fn($record) => $record->pfd_file ? Storage::url($record->pfd_file) : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('appointment.date')
                    ->label('Cita')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($record) => $record->appointment?->date ? $record->appointment->date->format('Y-m-d') : 'N/A'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                TrashedFilter::make(),
                
                Tables\Filters\SelectFilter::make('veterinarian_id')
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

                Tables\Filters\SelectFilter::make('mascota_id')
                    ->relationship('mascota', 'name')
                    ->label('Filtrar por Mascota'),
                Tables\Filters\SelectFilter::make('service_id')
                    ->relationship('service', 'name')
                    ->label('Filtrar por Servicio'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalRecords::route('/'),
            'create' => Pages\CreateMedicalRecord::route('/create'),
            'edit' => Pages\EditMedicalRecord::route('/{record}/edit'),
        ];
    }
}
