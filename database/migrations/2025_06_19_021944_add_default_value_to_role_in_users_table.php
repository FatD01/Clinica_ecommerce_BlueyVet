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
            // Modifica la columna 'role' para que tenga un valor por defecto.
            // Asegúrate de que 'role' ya existe y es de tipo string.
            // Si 'role' NO existe aún, deberías añadirla primero: $table->string('role')->default('Cliente')->after('email');
            $table->string('role')->default('Cliente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revierte el cambio quitando el valor por defecto.
            // Esto es importante para poder "deshacer" la migración si fuera necesario.
            $table->string('role')->default(null)->change();
        });
    }
};