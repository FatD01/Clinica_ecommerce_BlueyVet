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
        Schema::create('reprogramming_requests', function (Blueprint $table) {
            $table->id(); // Columna de clave primaria autoincremental
            
            // Clave foránea a la tabla 'appointments', para vincular la solicitud a una cita específica
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            
            // Claves foráneas directas a 'clientes' y 'veterinarians' para facilitar consultas y notificaciones
            // Esto desnormaliza un poco pero mejora la eficiencia del flujo de reprogramación.
            $table->foreignId('client_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->onDelete('cascade');
            
            // Tipo de entidad que inició esta propuesta de reprogramación ('veterinarian', 'client', 'admin')
            $table->string('requester_type', 20); 
            // Clave foránea al ID del usuario en la tabla 'users' que realizó esta solicitud.
            // Es nullable para permitir escenarios donde el usuario podría ser eliminado pero la solicitud se mantiene para auditoría.
            $table->foreignId('requester_user_id')->nullable()->constrained('users')->onDelete('set null'); 
            
            // Fechas y horas de inicio y fin propuestas en esta solicitud de reprogramación
            $table->dateTime('proposed_start_date_time'); // La fecha y hora de inicio que se está proponiendo
            $table->dateTime('proposed_end_date_time')->nullable(); // La fecha y hora de fin que se está proponiendo
            
            // Motivo específico por el cual se solicita o propone esta reprogramación
            $table->text('reprogramming_reason')->nullable();
            
            // Banderas booleanas para indicar si cada parte ha confirmado esta propuesta específica
            $table->boolean('client_confirmed')->default(false);
            $table->timestamp('client_confirmed_at')->nullable(); // Marca de tiempo de la confirmación del cliente
            
            $table->boolean('veterinarian_confirmed')->default(false);
            $table->timestamp('veterinarian_confirmed_at')->nullable(); // Marca de tiempo de la confirmación del veterinario
            
            // Estado actual de esta solicitud de reprogramación (su ciclo de vida)
            $table->enum('status', [
                'pending_client_confirmation',      // Propuesta enviada al cliente, esperando su respuesta
                'pending_veterinarian_confirmation',// Propuesta enviada al veterinario, esperando su respuesta
                'rejected_by_client',               // El cliente rechazó esta propuesta
                'rejected_by_veterinarian',         // El veterinario rechazó esta propuesta
                'accepted_by_both',                 // Ambas partes aceptaron esta propuesta
                'applied',                          // Esta propuesta fue aplicada a la cita principal (appointments)
                'cancelled_by_request',             // Esta solicitud de reprogramación fue anulada/cancelada por alguna de las partes o el admin
                'obsolete_by_new_proposal'          // Esta solicitud fue reemplazada por una nueva contrapropuesta
            ])->default('pending_client_confirmation'); // Establece un estado inicial por defecto.

            // Campo para notas internas del administrador sobre esta solicitud de reprogramación
            $table->text('admin_notes')->nullable();
            
            // Timestamps para created_at y updated_at del registro de la solicitud
            $table->timestamps();
            // Soporte para "soft deletes" para esta tabla
            $table->softDeletes();
            
            // Índices para mejorar el rendimiento de las consultas frecuentes
            $table->index('appointment_id');
            $table->index('client_id');
            $table->index('veterinarian_id');
            $table->index('status');
            $table->index(['requester_type', 'requester_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reprogramming_requests');
    }
};