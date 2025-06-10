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
        Schema::table('service_orders', function (Blueprint $table) {
            // Añade la columna 'payer_id' como string, que puede ser nula.
            // Lo ponemos después de 'paypal_order_id' para mantener un orden lógico,
            // pero esto es opcional, puede ir al final si lo prefieres.
            $table->string('payer_id')->nullable()->after('paypal_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // Cuando hagas rollback (php artisan migrate:rollback), esta columna se eliminará.
            $table->dropColumn('payer_id');
        });
    }
};