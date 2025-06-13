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
            // Esta columna vinculará una cita con su orden de servicio (pago) correspondiente
            $table->foreignId('service_order_id')
                  ->nullable() // Una cita puede ser creada primero y luego pagada, o podría ser gratuita.
                  ->constrained('service_orders') // Asegura que se vincule a tu tabla service_orders
                  ->onDelete('set null'); // O 'restrict' si prefieres evitar la eliminación de órdenes con citas vinculadas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_order_id');
        });
    }
};