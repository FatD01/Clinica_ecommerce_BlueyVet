<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('schedule_blocks', function (Blueprint $table) {
            $table->id();
            // Clave foránea para el veterinario asociado a este bloque de horario
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->onDelete('cascade');
            // Fecha y hora de inicio del bloque (ej. 2025-06-16 09:00:00)
            $table->dateTime('start_time');
            // Fecha y hora de fin del bloque (ej. 2025-06-16 13:00:00)
            $table->dateTime('end_time');
            // Indica si este bloque es parte de una serie de eventos recurrentes (semanales)
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_blocks');
    }
};