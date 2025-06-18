<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Método para procesar el login
    public function login(Request $request)
    {
        // Validación de campos
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Intento de autenticación
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Verifica si el rol es 'veterinario' (sin importar mayúsculas/minúsculas)
            if (strtolower($user->role) === 'veterinario') {
                $request->session()->regenerate();
                return redirect()->route('index');
            } else {
                // Si no tiene el rol correcto, se cierra la sesión inmediatamente
                Auth::logout();
                return back()->withErrors([
                    'email' => 'El acceso está permitido solo para usuarios con rol Veterinario.',
                ])->onlyInput('email');
            }
        }

        // Si falló el intento de login (email o contraseña incorrecta)
        return back()->withErrors([
            'email' => 'Correo o contraseña incorrectos.',
        ])->onlyInput('email');
    }

    // Método para logout
    public function logout(Request $request)
    {

        $user = Auth::user(); // ✅ Se guarda antes de cerrar sesión
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Redirigir según el rol que tenía el usuario
    if ($user && strtolower($user->role) === 'veterinario') {
         return redirect()->route('veterinarian.login'); // Login del veterinario
    }
        return redirect()->route('login');
    }
}
