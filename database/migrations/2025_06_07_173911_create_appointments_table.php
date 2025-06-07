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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade'); // Si cada cita es para una mascota
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->onDelete('cascade'); // Si cada cita es con un veterinario
            $table->dateTime('date'); // Fecha y hora de la cita
            $table->string('reason')->nullable(); // Motivo de la cita
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending'); // Estado de la cita
            $table->timestamps();
             $table->softDeletes(); // ¡Añade esto!
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};