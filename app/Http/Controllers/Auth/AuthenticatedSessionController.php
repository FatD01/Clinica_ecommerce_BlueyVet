<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Filament\Facades\Filament;
 // Asegúrate de que esto esté importado
use Illuminate\Support\Facades\URL;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
          // DD DE PRUEBA: ¿Llega aquí y cuál es la URL anterior?
        // dd([
        //     'LOCATION' => 'AuthenticatedSessionController create()',
        //     'URL Previous' => URL::previous(),
        //     'Request URL' => request()->fullUrl(),
        // ]);


        // Si la URL anterior (de donde vino la redirección) fue una ruta de admin,
        // o si estamos en el login y la URL actual contiene 'admin'
        if (
            str_contains(URL::previous(), '/admin') ||
            str_contains(request()->fullUrl(), '/admin')
        ) {
            // Si ya estamos en el login de Filament, no redirijas de nuevo.
            if (request()->fullUrl() === Filament::getLoginUrl()) {
                return view('auth.login'); // Muestra el login de cliente si es realmente el login de cliente
            }
            // Muestra una vista informando al usuario que debe iniciar sesión como admin
            return view('auth.filament-redirect', ['loginUrl' => Filament::getLoginUrl()]);
        }

        return view('auth.login'); // Para todas las demás situaciones, muestra el login de cliente.
    }
    

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();


        $user = Auth::user();
         $role = strtolower($user->role); // Convertimos el rol a minúsculas por seguridad

    if ($role === 'veterinario') {
        return redirect()->intended(route('index', absolute: false)); // Dashboard veterinario
    }

    // if ($role === 'admin') {
    //     return redirect()->intended(route('admin.dashboard', absolute: false)); // Dashboard admin
    // }

    if ($role === 'admin') {
    // Redirige al dashboard de Filament
        return redirect()->intended(Filament::getUrl());
    }   

        


        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {

        $user = Auth::user(); // ✅ Guarda el usuario antes de cerrar sesión
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

         // ✅ Redirigir según el rol
    if ($user && strtolower($user->role) === 'veterinario') {
        return redirect()->route('veterinarian.login');
    }

        return redirect('/');
    }
}
