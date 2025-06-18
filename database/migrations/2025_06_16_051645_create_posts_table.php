<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Opcional: si quieres asociar posts a un admin/usuario
            $table->string('title');
            $table->string('slug')->unique(); // Para URLs amigables (ej. /blog/mi-titulo-de-post)
            $table->longText('content'); // Contenido principal del post o respuesta del FAQ
            $table->string('excerpt')->nullable(); // Pequeño resumen o introducción
            $table->string('image_path')->nullable(); // Ruta a la imagen destacada del post
            $table->string('category')->nullable(); // Ej: 'perros', 'gatos', 'salud', 'preguntas-frecuentes'
            $table->boolean('is_published')->default(false); // Para publicar/despublicar posts
            $table->timestamp('published_at')->nullable(); // Fecha de publicación

            // Campo para diferenciar si es un post normal o un FAQ
            // Podrías usarlo para filtrar y mostrar solo los FAQs en una sección específica
            $table->enum('type', ['blog', 'faq'])->default('blog');

            $table->timestamps();
            $table->softDeletes(); // Para borrado suave, si lo necesitas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};