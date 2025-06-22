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
        Schema::table('users', function (Blueprint $table) {
            // Paso 1: Hacer el campo 'password' nullable
            // Esto permite que el campo sea nulo para usuarios de Google
            $table->string('password')->nullable()->change(); // Requiere doctrine/dbal

            // Paso 2: Añadir el nuevo campo 'provider'
            // Esto indicará si el usuario se registró con 'google' o 'email'
            $table->string('provider')->nullable()->after('password');

            // Nota: Ya tienes 'google_id', que funcionará como 'provider_id' para Google.
            // No necesitamos añadir un nuevo 'provider_id' genérico.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir los cambios si la migración se deshace
            // Si quieres que password vuelva a ser no-nullable:
            // $table->string('password')->nullable(false)->change();
            // Si prefieres que siga siendo nullable:
            // $table->string('password')->nullable()->change(); // No hace nada si ya es nullable
            $table->dropColumn('provider');
        });
    }
};