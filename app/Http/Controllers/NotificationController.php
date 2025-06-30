<?php

namespace App\Http\Controllers;

use App\Models\ReprogrammingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\DatabaseNotification; // Clase correcta para las notificaciones de la BD
// Asegúrate de que esta sea la notificación que usas para notificar al veterinario sobre la aceptación.
// Si no es esta, por favor, ajusta la importación y el constructor de la notificación.
use App\Notifications\ReprogramacionCitaNotification;
// Si tienes una notificación específica para Recordatorios, ya la manejamos en el toArray.

class NotificationController extends Controller
{
    /**
     * Muestra todas las notificaciones del usuario autenticado con paginación.
     */
    public function index()
    {
        $user = Auth::user();
        // Carga todas las notificaciones (leídas y no leídas) para mostrarlas en el índice.
        $notifications = $user->notifications()->paginate(10);

        // La vista será 'client.notifications.index' para ser coherente con la estructura.
        return view('notificaciones.index', compact('notifications'));
    }

    /**
     * Marca una notificación específica como leída.
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        // Asegúrate de que la notificación pertenece al usuario autenticado.
        if (Auth::id() !== $notification->notifiable_id) {
            Log::warning('Intento de marcar notificación no autorizada como leída.', [
                'user_id' => Auth::id(),
                'notification_id' => $notification->id,
            ]);
            abort(403, 'No tienes permiso para marcar esta notificación como leída.');
        }

        $notification->markAsRead(); // Marca la notificación como leída

        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }

    /**
     * Marca todas las notificaciones no leídas del usuario autenticado como leídas.
     */
    public function markAllAsRead()
    {
        // Usa `unreadNotifications` para solo marcar las que aún no han sido leídas.
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Todas tus notificaciones han sido marcadas como leídas.');
    }

    /**
     * Elimina una notificación específica.
     */
    public function destroy(DatabaseNotification $notification)
    {
        // Asegúrate de que la notificación pertenece al usuario autenticado.
        if ($notification->notifiable_id !== Auth::id()) {
            Log::warning('Intento de eliminar notificación no autorizada.', [
                'user_id' => Auth::id(),
                'notification_id' => $notification->id,
            ]);
            abort(403, 'No tienes permiso para eliminar esta notificación.');
        }

        $notification->delete();

        return redirect()->back()->with('success', 'Notificación eliminada correctamente.');
    }

    /**
     * Acepta una solicitud de reprogramación de cita.
     */
    public function aceptarReprogramacion($id)
    {
        $user = Auth::user();
        $cliente = $user->cliente; // Asumiendo que el User tiene una relación 'cliente'

        $reprogramacion = ReprogrammingRequest::with(['appointment', 'veterinarian.user'])->findOrFail($id);

        // 1. Verificación de permisos y estado
        if (!$cliente || $cliente->id !== $reprogramacion->client_id) {
            Log::warning('Intento de aceptar reprogramación no autorizada.', [
                'user_id' => Auth::id(),
                'reprogramming_request_id' => $id,
            ]);
            return redirect()->back()->with('error', 'No tienes permiso para aceptar esta reprogramación.');
        }

        if ($reprogramacion->status !== 'pending_client_confirmation') {
            return redirect()->back()->with('info', 'Esta solicitud ya ha sido procesada o no está pendiente de tu confirmación.');
        }

        DB::beginTransaction();

        try {
            // 2. Actualizar el estado de la solicitud de reprogramación
            $reprogramacion->update([
                'client_confirmed' => true,
                'client_confirmed_at' => now(),
                'status' => 'accepted_by_both',
            ]);

            // 3. Actualizar la cita original con las nuevas fechas
            $appointment = $reprogramacion->appointment;
            if ($appointment) {
                $appointment->date = $reprogramacion->proposed_start_date_time;
                $appointment->end_datetime = $reprogramacion->proposed_end_date_time;
                $appointment->status = 'reprogrammed'; // Puedes usar 'reprogrammed' o 'accepted' para el estado de la cita
                $appointment->save();
            } else {
                throw new \Exception('Cita asociada a la solicitud de reprogramación no encontrada para ID: ' . $reprogramacion->id);
            }

            // 4. Marcar la notificación específica del cliente como leída
            $notificationToMark = $user->unreadNotifications
                ->where('type', 'reprogramacion_propuesta_veterinario') // Asegúrate de que este 'type' coincida con lo que tu notificación guarda
                ->where('data->reprogramming_request_id', $reprogramacion->id) // Laravel 8+ soporta ->where('data->key', value)
                ->first();

            if ($notificationToMark) {
                $notificationToMark->markAsRead();
            }

            // 5. Notificar al veterinario que el cliente ha aceptado la reprogramación
            if ($reprogramacion->veterinarian && $reprogramacion->veterinarian->user) {
                $veterinarianUser = $reprogramacion->veterinarian->user;

                // Asegúrate de que el constructor de ReprogramacionCitaNotification acepte estos parámetros
                $veterinarianUser->notify(new ReprogramacionCitaNotification(
                    $reprogramacion->appointment, // La cita original que se reprogramó
                    $reprogramacion->proposed_start_date_time, // La nueva fecha/hora
                    'El cliente ha aceptado la reprogramación de la cita de ' . $cliente->user->name . ' (' . $reprogramacion->appointment->mascota->name . ').', // Mensaje claro para el veterinario
                    $reprogramacion // El objeto de reprogramación completo
                ));
            } else {
                Log::warning('No se pudo encontrar el usuario del veterinario para notificar sobre la reprogramación aceptada.', [
                    'reprogramming_request_id' => $id,
                    'veterinarian_id' => $reprogramacion->veterinarian_id ?? 'N/A'
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', '¡Reprogramación aceptada y cita actualizada correctamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aceptar reprogramación: ' . $e->getMessage(), [
                'reprogramming_request_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Ocurrió un error al procesar la aceptación. Por favor, inténtalo de nuevo.');
        }
    }
}