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
        Schema::table('posts', function (Blueprint $table) {
            // Modifica la columna 'content' para que AHORA SÍ pueda ser nula
            // Esto es crucial. Usa longText porque es el tipo de dato que soporta el contenido HTML/JSON del editor.
            $table->longText('content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Si haces rollback, revierte la columna a NOT NULL.
            // TEN MUCHO CUIDADO AQUÍ: Si hay posts con 'content' nulo al hacer rollback, fallará.
            // Si estás en desarrollo, puedes usar 'php artisan migrate:rollback' y luego 'php artisan migrate'
            // para empezar de cero si no tienes datos importantes.
            $table->longText('content')->nullable(false)->change();
        });
    }
};