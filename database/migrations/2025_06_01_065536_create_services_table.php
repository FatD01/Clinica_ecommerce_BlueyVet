// database/migrations/YYYY_MM_DD_XXXXXX_create_services_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->integer('duration_minutes')->nullable();
            $table->string('status')->default('active'); // O un enum si prefieres
            $table->timestamps();
             $table->softDeletes(); // ¡Añade esto!
        });
    }

 
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};