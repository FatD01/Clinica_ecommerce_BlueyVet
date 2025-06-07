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
        Schema::table('medical_records', function (Blueprint $table) {
            // 1. Añadir appointment_id (FK)
            // Asegúrate de que la tabla 'appointments' ya existe (¡muy importante!)
            // Si no existe, debes crearla primero con su propia migración y modelo.
            $table->foreignId('appointment_id')
                  ->nullable() // Permite que sea opcional si un registro médico no tiene una cita
                  ->constrained('appointments') // La clave foránea apunta a la tabla 'appointments'
                  ->onDelete('set null') // Si se elimina una cita, este campo se pondrá a NULL
                  ->after('notes'); // Colócala después de la columna 'notes'

            // 2. Añadir prescription (TEXT)
            $table->text('prescription')->nullable()->after('appointment_id'); // Coloca después de appointment_id

            // 3. Añadir observations (TEXT)
            // Asumo que 'observations' es diferente de 'notes'
            $table->text('observations')->nullable()->after('prescription'); // Coloca después de prescription

            // 4. Añadir pfd_file (VARCHAR para la ruta del archivo)
            $table->string('pfd_file')->nullable()->after('observations'); // Coloca después de observations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Es crucial eliminar primero la clave foránea antes de la columna asociada
            $table->dropForeign(['appointment_id']);
            // Luego, elimina todas las columnas añadidas en el mismo orden inverso
            $table->dropColumn(['appointment_id', 'prescription', 'observations', 'pfd_file']);
        });
    }
};