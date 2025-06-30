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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();

            // CLAVE FORÁNEA PARA EL CLIENTE:
            // Apuntamos a la tabla 'clientes', ya que tu Cliente es el dueño real del recordatorio
            // y el que tiene el email a través de la relación con 'users'.
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Clave foránea para la mascota a la que se refiere el recordatorio
            $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade');

            // Campos para la relación polimórfica (opcional, para vincular a MedicalRecord, Appointment, etc.)
            $table->string('related_to_type')->nullable(); // Ej: 'App\Models\MedicalRecord'
            $table->unsignedBigInteger('related_to_id')->nullable(); // El ID del MedicalRecord
            $table->index(['related_to_type', 'related_to_id']);

            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('remind_at');
            $table->dateTime('sent_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};