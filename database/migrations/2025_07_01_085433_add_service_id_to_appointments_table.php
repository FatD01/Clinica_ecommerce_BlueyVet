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
            // Añade service_id si no existe
            if (!Schema::hasColumn('appointments', 'service_id')) {
                // Basado en tu info anterior: bigint(20) UNSIGNED, puede ser NULL, es FK
                $table->foreignId('service_id')->nullable()->constrained('services')->after('veterinarian_id');
                // ^ Asegúrate que 'services' es el nombre correcto de tu tabla de servicios
                // ^ 'nullable()' porque dijiste que puede ser NULL
            }

            // Si 'deleted_at' también falta en Railway y la usas para Soft Deletes:
            if (!Schema::hasColumn('appointments', 'deleted_at')) {
                $table->softDeletes(); // Esto crea la columna deleted_at
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'service_id')) {
                $table->dropForeign(['service_id']); // Elimina la FK primero
                $table->dropColumn('service_id');
            }
            if (Schema::hasColumn('appointments', 'deleted_at')) {
                $table->dropSoftDeletes(); // Esto elimina la columna deleted_at
            }
        });
    }
};