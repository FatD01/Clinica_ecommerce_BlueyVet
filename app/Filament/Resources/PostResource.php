<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str; // Importar Str para slugs

// Importaciones para los campos
use Filament\Forms\Components\DatePicker; // Para seleccionar fecha de publicación
use Filament\Forms\Components\Select; // Para el tipo de post y categoría
use Filament\Forms\Components\Toggle; // Para is_published
use Filament\Forms\Components\FileUpload; // Para subir imágenes

// Importación CLAVE: El TiptapEditor de Awcodes
use FilamentTiptapEditor\TiptapEditor; // ¡Esta es la importación correcta para el campo!

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
       protected static ?string $navigationGroup = 'Administración y Contenido';
    protected static ?string $label = 'Artículo/FAQ'; // Etiqueta singular en el menú
    protected static ?string $pluralLabel = 'Artículos y FAQs'; // Etiqueta plural en el menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make() // Usar Card para agrupar campos
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true) // Genera el slug al salir del campo
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true), // Asegura que el slug sea único, ignora el registro actual al editar

                        // --- CAMBIO AQUÍ: Usando TiptapEditor de Awcodes ---
                        // ESTE ES EL CAMPO PARA EL CONTENIDO PRINCIPAL DEL POST
                        TiptapEditor::make('content') // El nombre del campo en tu base de datos (e.g., 'content')
                            ->profile('default') // Usa el perfil 'default' (configurado en config/filament-tiptap-editor.php)
                            ->disk('public') // O el disco que uses para guardar las imágenes y archivos subidos por el editor
                            ->directory('post-content-images') // Carpeta específica dentro de tu disco 'public' para las imágenes del contenido
                            ->columnSpanFull() // Ocupa todo el ancho disponible
                            ->label('Contenido')
                            ->required()
                            ->extraAttributes([ // <-- Añade esto para CSS inline
                                'style' => 'min-height: 300px;', // Ajusta el valor según tu preferencia (ej. 400px, 500px)
                            ]),

                        Forms\Components\TextInput::make('excerpt')
                            ->maxLength(255)
                            ->label('Extracto (resumen corto)')
                            ->helperText('Un resumen corto del contenido. Si se deja vacío, se generará automáticamente.'),

                        FileUpload::make('image_path')
                            ->label('Imagen destacada')
                            ->image() // Acepta solo imágenes
                            ->directory('blog-images') // Carpeta dentro de storage/app/public/
                            ->nullable(),

                        Select::make('category')
                            ->options([
                                'perros' => 'Perros',
                                'gatos' => 'Gatos',
                                'salud' => 'Salud General',
                                'alimentacion' => 'Alimentación',
                                'comportamiento' => 'Comportamiento',
                                'otros' => 'Otros',
                            ])
                            ->default('otros')
                            ->label('Categoría'),

                        Select::make('type') // Campo para diferenciar Blog de FAQ
                            ->options([
                                'blog' => 'Artículo de Blog',
                                'faq' => 'Pregunta Frecuente (FAQ)',
                            ])
                            ->required()
                            ->default('blog')
                            ->label('Tipo de Publicación'),

                        Toggle::make('is_published')
                            ->label('Publicado')
                            ->default(false),

                        DatePicker::make('published_at')
                            ->label('Fecha de Publicación')
                            ->nullable()
                            ->default(now()), // Por defecto, la fecha actual
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->label('Título'),
                Tables\Columns\TextColumn::make('type')
                    ->badge() // Muestra un "badge" visual (Blog/FAQ)
                    ->color(fn(string $state): string => match ($state) {
                        'blog' => 'success',
                        'faq' => 'info',
                        default => 'gray',
                    })
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->label('Categoría'),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Publicado'),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha Publicación'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true) // Oculto por defecto
                    ->label('Última Actualización'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'blog' => 'Artículos de Blog',
                        'faq' => 'Preguntas Frecuentes',
                    ])
                    ->label('Filtrar por Tipo'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Estado de Publicación')
                    ->trueLabel('Publicado')
                    ->falseLabel('Borrador')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Si implementas comentarios y los quieres moderar desde aquí, irían como RelationManager
            // RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
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
