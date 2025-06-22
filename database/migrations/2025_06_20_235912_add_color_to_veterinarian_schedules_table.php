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
        Schema::table('veterinarian_schedules', function (Blueprint $table) {
            // Añade la columna 'color' de tipo string, permitiendo valores nulos
            // Puedes ajustar 'after' según donde quieras la columna en tu DB
            $table->string('color')->nullable()->after('end_time'); // Por ejemplo, después de 'end_time'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('veterinarian_schedules', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};