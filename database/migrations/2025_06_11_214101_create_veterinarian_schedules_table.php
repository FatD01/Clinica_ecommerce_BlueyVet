// database/migrations/YYYY_MM_DD_create_veterinarian_schedules_table.php

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
        Schema::create('veterinarian_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinarian_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('0 for Sunday, 1 for Monday, ..., 6 for Saturday');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            // Asegura que un veterinario no tenga horarios duplicados para el mismo día
            $table->unique(['veterinarian_id', 'day_of_week', 'start_time'], 'unique_vet_day_time_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinarian_schedules');
    }
};