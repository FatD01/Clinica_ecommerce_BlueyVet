<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification; // Importar el modelo de notificación

class NotificationController extends Controller
{
    /**
     * Muestra todas las notificaciones del usuario autenticado.
     * Marca las notificaciones como leídas automáticamente.
     */
    public function index()
    {
        $user = auth()->user();

        // Cargar notificaciones y marcarlas como leídas
        // Puedes elegir cargar solo las no leídas o todas.
        // Para una página de listado, generalmente querrás todas.
        $notifications = $user->notifications()->paginate(15); // Pagina las notificaciones

        // Marcar todas las notificaciones NO LEÍDAS como leídas al visitar esta página
        $user->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Muestra una notificación específica y la marca como leída.
     */
    public function show(DatabaseNotification $notification)
    {
        // Asegúrate de que el usuario actual es el dueño de la notificación
        if (auth()->id() !== $notification->notifiable_id) {
            abort(403); // O redirige a un error 403 si no es el dueño
        }

        $notification->markAsRead(); // Marca la notificación como leída

        // Redirige a la URL asociada a la notificación o a una página de detalle
        // Si la notificación tiene una 'url' en sus datos, redirige ahí.
        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        // Si no hay URL específica, puedes mostrar una vista de detalle de la notificación
        return view('notifications.show', compact('notification'));
    }

    /**
     * Marca una notificación específica como leída (útil para botones de "marcar como leído").
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        if (auth()->id() !== $notification->notifiable_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Marca todas las notificaciones del usuario como leídas.
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Todas las notificaciones han sido marcadas como leídas.');
    }
}