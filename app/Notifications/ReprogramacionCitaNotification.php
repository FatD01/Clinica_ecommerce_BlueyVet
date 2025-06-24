<?php

namespace App\Notifications; // Asegúrate de que el namespace sea solo App\Notifications

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Notifications\Messages\DatabaseMessage; // No es necesario si usas toDatabase directamente
use Carbon\Carbon;
use App\Models\Appointment; // Necesitarás importar el modelo Appointment
use App\Models\ReprogrammingRequest; // Necesitarás importar el modelo ReprogrammingRequest

class ReprogramacionCitaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $cita;
    protected $nuevaFecha;
    protected $motivo;
    protected $reprogrammingRequest;
    protected $isAcceptedNotification; // <-- NUEVA PROPIEDAD para distinguir

    public function __construct(
        Appointment $cita, // Tipo para hinting
        Carbon $nuevaFecha, // Tipo para hinting
        $motivo = null,
        ReprogrammingRequest $reprogrammingRequest = null, // Tipo para hinting
        bool $isAcceptedNotification = false // <-- NUEVO PARÁMETRO
    ) {
        $this->cita = $cita;
        $this->nuevaFecha = $nuevaFecha;
        $this->motivo = $motivo;
        $this->reprogrammingRequest = $reprogrammingRequest;
        $this->isAcceptedNotification = $isAcceptedNotification; // Asignar nueva propiedad
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        // Lógica condicional para el mensaje según si es una notificación de aceptación
        if ($this->isAcceptedNotification) {
            $clientName = $this->reprogrammingRequest->client->user->name ?? 'Un cliente';
            $oldDate = $this->cita->date->format('d/m/Y H:i'); // La fecha original de la cita
            $newDate = $this->nuevaFecha->format('d/m/Y H:i');

            return [
                'title' => '¡Reprogramación Aceptada por Cliente!',
                'body' => "El cliente {$clientName} ha aceptado la reprogramación de la cita #{$this->cita->id} del {$oldDate} para el {$newDate}. Motivo: " . ($this->motivo ?? 'Sin motivo específico.'),
                'appointment_id' => $this->cita->id,
                'reprogramming_request_id' => $this->reprogrammingRequest->id ?? null,
                'type' => 'reprogramacion_aceptada_cliente', // Un tipo específico para el veterinario
            ];
        } else {
            // Lógica existente para cuando el veterinario propone al cliente
            return [
                'title' => 'Propuesta de Reprogramación de Cita', // Título más claro para el cliente
                'body' => 'Tu cita ha sido propuesta para el ' . $this->nuevaFecha->format('d/m/Y H:i') .
                          ($this->motivo ? ('. Motivo: ' . $this->motivo) : ''),
                'appointment_id' => $this->cita->id,
                'reprogramming_request_id' => $this->reprogrammingRequest->id ?? null,
                'type' => 'reprogramacion_propuesta_veterinario', // Tipo para la notificación al cliente
            ];
        }
    }
}