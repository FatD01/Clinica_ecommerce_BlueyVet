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
            $table->dateTime('date')->change();
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