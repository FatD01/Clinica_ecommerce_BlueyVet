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
        Schema::table('products', function (Blueprint $table) {
            // Añade la nueva columna 'min_stock_threshold'
            // Recomiendo que sea un entero (o smallInteger si los valores son pequeños)
            // y que tenga un valor por defecto, por ejemplo, 5 o 10.
            // Es bueno que sea nullable si no todos los productos necesitan un umbral específico.
            $table->integer('min_stock_threshold')->default(10)->after('stock');
            // Alternativamente, si puede ser nulo y no tiene un valor por defecto inicial:
            // $table->integer('min_stock_threshold')->nullable()->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Elimina la columna si se revierte la migración
            $table->dropColumn('min_stock_threshold');
        });
    }
};