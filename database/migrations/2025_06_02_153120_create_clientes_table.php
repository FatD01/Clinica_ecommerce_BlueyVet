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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relación con la tabla de usuarios
            $table->string('nombre'); // Nombre del dueño (opcional, puede estar en users)
            $table->string('apellido')->nullable(); // Apellido del dueño (opcional, puede estar en users)
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->timestamps();
             $table->softDeletes(); // ¡Añade esto!
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
