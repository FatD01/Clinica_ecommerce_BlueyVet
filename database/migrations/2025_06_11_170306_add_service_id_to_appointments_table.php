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
            // Asegúrate de que 'reason' (string) ya existe o añádelo si lo eliminaste antes.
            // Si tu migración original de 'appointments' ya lo tenía, no necesitas hacer nada aquí para 'reason'.
            // Ejemplo si lo necesitas: $table->string('reason')->nullable()->after('date');

            // Añadir service_id como clave foránea
            $table->foreignId('service_id')
                  ->nullable() // Permite que sea null si un servicio no es siempre obligatorio (aunque tu flujo lo hace requerido)
                  ->constrained('services') // Asume que ya tienes una tabla 'services'
                  ->after('veterinarian_id') // Posición en la tabla, ajústala si quieres
                  ->onDelete('set null'); // Si un servicio es eliminado, las citas asociadas no se borran sino que su service_id se vuelve null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            // Si añadiste 'reason' en el 'up' de esta migración, también lo eliminarías aquí:
            // $table->dropColumn('reason');
        });
    }
};