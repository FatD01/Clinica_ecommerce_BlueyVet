<?php

namespace App\Http\Controllers;

use App\Models\ReprogrammingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- ¡Importa la fachada DB!
use Illuminate\Support\Facades\Log; // <-- Importa la fachada Log para evitar el error
use App\Notifications\Client\ReprogrammingRequestAccepted; // <-- Asegúrate de tener esta notificación (opcional, para notificar al veterinario)
use Illuminate\Notifications\DatabaseNotification; // <-- Importa la clase correcta para DatabaseNotification
use App\Notifications\ReprogramacionCitaNotification; // <-- Importa la notificación correcta para reprogramación de cita

class NotificationController extends Controller
{
    public function aceptarReprogramacion($id)
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        $reprogramacion = ReprogrammingRequest::with(['appointment', 'veterinarian.user'])->findOrFail($id);

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
            $reprogramacion->update([
                'client_confirmed' => true,
                'client_confirmed_at' => now(),
                'status' => 'accepted_by_both',
            ]);

            $appointment = $reprogramacion->appointment;
            if ($appointment) {
                $appointment->date = $reprogramacion->proposed_start_date_time;
                $appointment->end_datetime = $reprogramacion->proposed_end_date_time;
                $appointment->status = 'reprogrammed';
                $appointment->save();
            } else {
                throw new \Exception('Cita asociada a la solicitud de reprogramación no encontrada para ID: ' . $reprogramacion->id);
            }

            $notificationToMark = $user->unreadNotifications
                ->where('data.reprogramming_request_id', $reprogramacion->id)
                ->first();
            if ($notificationToMark) {
                $notificationToMark->markAsRead();
            }

            // 4. Notifica al veterinario que el cliente ha aceptado la reprogramación
            if ($reprogramacion->veterinarian && $reprogramacion->veterinarian->user) {
                $veterinarianUser = $reprogramacion->veterinarian->user;

                // *** CAMBIO AQUÍ: Usamos tu clase ReprogramacionCitaNotification
                // *** y le pasamos los parámetros que necesita.
                // Para este caso, la 'cita' sería la reprogramación, la 'nueva fecha' la de la reprogramación,
                // y el 'motivo' el de la reprogramación.
                $veterinarianUser->notify(new ReprogramacionCitaNotification(
                    $reprogramacion->appointment, // Pasamos la cita original
                    $reprogramacion->proposed_start_date_time, // La nueva fecha propuesta
                    'El cliente ha aceptado la reprogramación', // Un motivo genérico para esta notificación
                    $reprogramacion // Pasamos el objeto de reprogramación
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

    public function markAsRead(Request $request, DatabaseNotification $notification)
    {
        $notification->markAsRead(); // Marca la notificación como leída

        // Puedes redirigir a la misma página de notificaciones o a donde sea útil.
        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }

    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(10);

        return view('notificaciones.index', compact('notifications'));
    }

    public function clearRead()
    {
        $user = Auth::user();

        // Elimina solo las notificaciones que ya han sido leídas
        $user->readNotifications->each(function ($notification) {
            $notification->delete();
        });

        return redirect()->back()->with('success', 'Todas las notificaciones leídas han sido eliminadas.');
    }

    public function destroy(DatabaseNotification $notification)  // <-- ¡MÉTODO DESTROY AQUÍ!
    {
        if ($notification->notifiable_id !== Auth::id()) {
            abort(403, 'No tienes permiso para eliminar esta notificación.');
        }

        $notification->delete();

        return redirect()->back()->with('success', 'Notificación eliminada correctamente.');
    }
}
