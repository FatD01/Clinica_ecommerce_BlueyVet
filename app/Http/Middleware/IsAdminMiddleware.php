<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    /**
     * Maneja una petición entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Primero, verifica si el usuario está autenticado.
        // Si no hay un usuario logueado, redirige al login.
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para acceder al panel de administración.');
        }

        // 2. Si el usuario está autenticado, verifica su rol.
        // Se compara estrictamente con 'admin' en minúsculas.
        // Asegúrate de que los usuarios administradores en tu base de datos tengan el valor 'admin' en la columna 'role'.
        if (strtolower(Auth::user()->role) === 'admin') {
            return $next($request); // Permite el acceso si el rol es 'admin'
        }

        // 3. Si el usuario está autenticado pero su rol NO es 'admin', redirige.
        // Por ejemplo, un "Cliente" o "Veterinario" será redirigido aquí.
        return redirect('/')->with('error', 'No tienes permiso para acceder a esta sección.');
        // Si prefieres un error HTTP 403 (Prohibido): abort(403, 'Acceso no autorizado.');
    }
}