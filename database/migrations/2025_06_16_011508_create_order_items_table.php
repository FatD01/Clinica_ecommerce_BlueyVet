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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Relación con la tabla 'orders'
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null'); // Relación con tu tabla 'products', si existe
            $table->string('name'); // Nombre del producto en el momento de la compra
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Precio unitario del producto en el momento de la compra
            $table->timestamps();
            // No se recomienda softDeletes para order_items si ya se elimina en cascada con la orden
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};