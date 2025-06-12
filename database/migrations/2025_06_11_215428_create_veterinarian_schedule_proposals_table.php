// database/migrations/YYYY_MM_DD_create_veterinarian_schedule_proposals_table.php

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
        Schema::create('veterinarian_schedule_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinarian_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable()->comment('Solo para propuestas de tipo "exception"'); // Fecha específica para una excepción
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Dom, 1=Lun,..., 6=Sab; Solo para propuestas de tipo "recurring"'); // Día de la semana para un horario recurrente
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['recurring', 'exception'])->comment('Tipo de propuesta: recurrente o excepción');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Estado de la propuesta: pendiente, aprobada, rechazada');
            $table->text('reason')->nullable()->comment('Motivo o explicación de la propuesta por el veterinario');
            $table->timestamps();

            // Índices compuestos para mejorar el rendimiento
            $table->index(['veterinarian_id', 'status']);
            $table->index(['veterinarian_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinarian_schedule_proposals');
    }
};