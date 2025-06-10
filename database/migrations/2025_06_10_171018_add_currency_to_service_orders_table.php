<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyToServiceOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // Añade la columna 'currency' como un string de 3 caracteres (ej: 'PEN', 'USD')
            // y establece 'PEN' como valor por defecto. La colocamos después de 'amount'.
            $table->string('currency', 3)->default('PEN')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // Para revertir la migración, eliminamos la columna 'currency'
            $table->dropColumn('currency');
        });
    }
}