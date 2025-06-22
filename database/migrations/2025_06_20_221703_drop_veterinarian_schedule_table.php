<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // esta migracion es para eliminar la tabla para hacer el calendario que no funcionaba
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('veterinarian_schedule');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('veterinarian_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->onDelete('cascade');
            $table->json('day_of_week'); // ¡DEFINIMOS 'day_of_week' como JSON desde el principio!
            $table->time('start_time');
            $table->time('end_time');
            $table->string('notes')->nullable();
            $table->string('color')->default('#3b82f6');
            $table->timestamps();
        });
    }
};
