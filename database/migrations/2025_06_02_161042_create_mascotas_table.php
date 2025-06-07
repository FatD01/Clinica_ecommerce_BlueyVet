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
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade'); // Relación con la tabla de clientes
            $table->string('name');
            $table->string('species')->nullable(); // Especie
            $table->string('race')->nullable(); // Raza
            $table->float('weight')->nullable(); // Peso
            $table->date('birth_date')->nullable(); // Fecha de nacimiento
            $table->text('allergies')->nullable(); // Alergias
            $table->string('image', 255)->nullable(); //imagen para la moscota
            // user_id ya no es FK aquí, la relación es a través de clientes
            $table->timestamps();
             $table->softDeletes(); // ¡Añade esto!
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
