<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable(false);           
            $table->text('description')->nullable();           
            $table->decimal('price', 8, 2)->nullable(false);          
            $table->integer('stock')->default(0)->nullable(false);         
            $table->string('category', 100);
            $table->boolean('promotion_active')->default(false);
            $table->string('image', 255)->nullable();
            $table->timestamps();
        });
        
    }
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
