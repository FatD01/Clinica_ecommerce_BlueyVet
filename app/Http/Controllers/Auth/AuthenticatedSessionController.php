<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
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

    if ($role === 'admin') {
        return redirect()->intended(route('admin.dashboard', absolute: false)); // Dashboard admin
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
