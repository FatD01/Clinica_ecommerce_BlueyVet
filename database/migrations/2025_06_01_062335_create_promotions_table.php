// database/migrations/YYYY_MM_DD_HHMMSS_create_promotions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable(false);
            $table->text('description')->nullable();

            // Nuevo campo: A qué se aplica la promoción (producto o servicio)
            $table->enum('apply_to', ['product', 'service'])->nullable(false);

            // Campo para el tipo de descuento
            $table->enum('discount_type', ['none', 'percentage', 'fixed_amount', 'buy_x_get_y'])->default('none');

            // Valor del descuento (usado para percentage o fixed_amount)
            $table->decimal('discount_value', 8, 2)->nullable();

            // Campos para promociones 'buy_x_get_y'
            $table->integer('buy_quantity')->nullable(); // Para X en 'Compra X'
            $table->integer('get_quantity')->nullable(); // Para Y en 'Lleva Y Gratis'

            $table->date('start_date')->nullable(false);
            $table->date('end_date')->nullable(false);

            // Opcional: Campo booleano para activar/desactivar manualmente
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};