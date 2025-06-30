<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reminder; // Importa el modelo Reminder
use App\Models\Client; // Importa el modelo Client
use App\Models\Cliente;
use App\Models\User; // Importa el modelo User
use Carbon\Carbon; // Para trabajar con fechas
use Illuminate\Support\Facades\Log; // Importa el facade Log

class ReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Reminder $reminder;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reminder $reminder)
    {
         $this->reminder = $reminder->loadMissing('mascota.cliente');

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // El cliente (modelo Cliente) recibirá la notificación por base de datos y email
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {// *** AÑADE ESTE LOG AQUÍ PARA VER SI EL WORKER PROCESA EL CORREO ***
       

        // $userName = $notifiable->user->name;
        Log::info('ReminderNotification::toMail se está ejecutando para recordatorio ID: ' . $this->reminder->id . ' - Version Corregida');

            // *** CAMBIO CRUCIAL AQUÍ ***
            // Asegúrate de que el $notifiable (que es el cliente) tenga su relación 'user' cargada.
            // Si tu modelo Client tiene una relación 'user', la cargamos.
            if ($notifiable instanceof Cliente) { // O el tipo de tu modelo de cliente
                $notifiable->loadMissing('user');
                $userName = $notifiable->user ? $notifiable->user->name : 'Estimado Cliente';
            } elseif ($notifiable instanceof User) { // Si el notifiable es directamente un User
                $userName = $notifiable->name;
            } else {
                $userName = 'Estimado Cliente'; // Fallback por si acaso
            }
        $petName = $this->reminder->mascota->name;

        $formattedReminderText = $this->reminder->formatted_reminder_text;

        return (new MailMessage)
                    ->subject('Recordatorio de BlueyVet: ' . $this->reminder->title)
                    ->greeting('Hola ' . $userName . ',')
                    ->line($formattedReminderText)
                    ->line('**Detalles Adicionales:** ' . $this->reminder->description)
                    ->action('Ver tus Recordatorios', route('client.mascotas.show', $this->reminder->mascota_id) . '#reminders')
                    ->line('Gracias por confiar en BlueyVet.')
                    ->salutation('Saludos,');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // *** AÑADE ESTE LOG AQUÍ PARA VER SI EL toArray SE EJECUTA ***
    Log::info('ReminderNotification::toArray se está ejecutando para recordatorio ID: ' . $this->reminder->id);

    // También aseguramos el nombre en toArray si es necesario para el dashboard
    if ($notifiable instanceof Cliente) { // Asegúrate de usar 'Cliente' si ese es tu modelo
        $notifiable->loadMissing('user');
        $userNameForArray = $notifiable->user ? $notifiable->user->name : 'Estimado Cliente';
    } elseif ($notifiable instanceof User) {
        $userNameForArray = $notifiable->name;
    } else {
        $userNameForArray = 'Estimado Cliente';
    }

    return [
        'type'           => 'App\\Notifications\\ReminderNotification',
        'reminder_id'    => $this->reminder->id,
        'title'          => $this->reminder->title,
        'pet_name'       => $this->reminder->mascota->name,
        'description'    => $this->reminder->description,
        'remind_at'      => $this->reminder->remind_at->toDateTimeString(),
        'formatted_text' => $this->reminder->formatted_reminder_text,
        'link'           => route('client.mascotas.show', $this->reminder->mascota_id) . '#reminders',
        'client_name'    => $userNameForArray, // Añadir el nombre del cliente al array de notificación
    ];
   
    }
}