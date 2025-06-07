<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('product_promotion', function (Blueprint $table) {
            $table->id(); 
            // Clave foránea para products
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            // Clave foránea para promotions
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->timestamps(); 
            $table->unique(['product_id', 'promotion_id']);
             $table->softDeletes(); // ¡Añade esto!
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_promotion');
    }
};
