<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necesario para obtener el usuario autenticado
use App\Models\Order; // Aunque no lo uses directamente para la consulta principal, es buena práctica
use App\Models\User; // Importa el modelo User

class ClientOrderController extends Controller
{
    /**
     * Muestra la lista de pedidos del usuario autenticado.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtiene el usuario actualmente autenticado.
        // Dado que Auth::user() devuelve una instancia de App\Models\User,
        // podemos acceder directamente a la relación 'orders()'.
        $user = Auth::user();

        // Es una buena práctica verificar si el usuario está realmente autenticado,
        // aunque el middleware 'auth' ya lo haga.
        if (!$user) {
            // Esto debería ser poco probable si la ruta está protegida.
            // Podrías redirigir a la página de login o mostrar un mensaje de error.
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver tus pedidos.');
        }

        // Carga los pedidos del usuario autenticado.
        // Usamos eager loading con 'items.product' para cargar los OrderItems y sus Products relacionados
        // en una sola consulta, evitando el problema de N+1 queries.
        $orders = $user->orders()->with('items.product')->latest()->get();

        // Retorna la vista 'client.profile.orders' y le pasa la colección de pedidos.
        // La variable $orders estará disponible en tu archivo orders.blade.php
        return view('client.profile.orders', compact('orders'));
    }
}