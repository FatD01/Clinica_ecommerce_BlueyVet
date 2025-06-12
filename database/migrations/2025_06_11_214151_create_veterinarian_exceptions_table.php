// database/migrations/YYYY_MM_DD_create_veterinarian_exceptions_table.php

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
        Schema::create('veterinarian_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinarian_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time')->nullable(); // Nullable si es una excepción de día completo
            $table->time('end_time')->nullable();   // Nullable si es una excepción de día completo
            $table->enum('type', ['available', 'unavailable'])->default('unavailable'); // 'available' para añadir horas extras, 'unavailable' para feriados/descansos
            $table->string('notes')->nullable(); // Notas sobre la excepción (ej. "Feriado", "Vacaciones")
            $table->timestamps();

            // Asegura que un veterinario no tenga excepciones duplicadas para la misma fecha
            $table->unique(['veterinarian_id', 'date', 'start_time'], 'unique_vet_exception');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinarian_exceptions');
    }
};