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
        Schema::table('appointments', function (Blueprint $table) {
            // Paso 1: Asegurar que la columna 'date' sea de tipo DATETIME.
            // Si 'date' es actualmente solo DATE (sin hora), esta línea lo modificará a DATETIME.
            // Si 'date' ya es DATETIME, esta línea es segura y no hará cambios.
            // Esto es crucial para poder almacenar la hora de inicio de la cita.
            $table->dateTime('date')->change();

            // Paso 2: Añadir la nueva columna 'end_datetime' de tipo DATETIME.
            // `nullable()` permite que las citas existentes no fallen, ya que su valor será NULL inicialmente.
            // `after('date')` coloca esta nueva columna justo después de la columna 'date' en tu tabla.
            $table->dateTime('end_datetime')->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Paso 1 (Reverso): Eliminar la columna 'end_datetime' si la migración se revierte.
            $table->dropColumn('end_datetime');

            // Paso 2 (Reverso - OPCIONAL):
            // Si eres ABSOLUTAMENTE CERTERO de que tu columna 'date' era de tipo DATE antes
            // y quieres que vuelva a serlo si la migración se revierte, descomenta la siguiente línea.
            // En la mayoría de los casos, si ya la convertiste a DATETIME, querrás que siga así.
            // $table->date('date')->change();
        });
    }
};