<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // ¡No olvides importar Rule!

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        // Carga la relación 'cliente' o 'veterinarian' si el usuario autenticado tiene ese rol.
        // Esto asegura que la vista tenga acceso a los datos específicos del perfil.
        $user = $request->user()->load(['cliente', 'veterinarian']);

        return view('client.profile.profile', [
            'user' => $user,
        ]);
    }

    public function updatePersonal(Request $request)
    {
        $user = $request->user();

        // Reglas de validación para campos del modelo User
        $userRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ];

        // Validar campos del modelo User
        $validatedUser = $request->validate($userRules);

        $user->fill($validatedUser);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        // Lógica ESPECÍFICA para Cliente
        if ($user->role === 'Cliente' && $user->cliente) {
            // Reglas de validación para campos específicos del Cliente
            $clienteRules = [
                'telefono' => ['nullable', 'string', 'max:20'],
                'direccion' => ['nullable', 'string', 'max:255'],
            ];

            // Añadir la regla para 'apellido' SOLO si el usuario NO es de Google
            if ($user->provider !== 'google') {
                $clienteRules['apellido'] = ['required', 'string', 'max:255'];
                // Podrías cambiar a 'nullable' aquí si quieres que, incluso para usuarios de email,
                // el apellido no sea estrictamente obligatorio, pero si lo es, déjalo como 'required'.
                // 'apellido' => ['nullable', 'string', 'max:255'],
            }

            $validatedCliente = $request->validate($clienteRules);

            // Preparar los datos a actualizar para el cliente
            $updateData = $request->only('telefono', 'direccion');
            if ($user->provider !== 'google') {
                $updateData['apellido'] = $validatedCliente['apellido'];
            }

            $user->cliente->update($updateData);
        }
        // // Lógica ESPECÍFICA para Veterinario (si se implementa)
        // elseif ($user->role === 'Veterinario' && $user->veterinarian) {
        //     // ... Tu lógica de validación y actualización para veterinario
        // }

        // ¡IMPORTANTE! Recargar el usuario autenticado y sus relaciones
        // Esto es crucial para que el navbar y otras partes de la aplicación
        // tengan acceso a los datos más recientes y el "punto" se actualice.
        // Solo recargar 'cliente' o 'veterinarian' según el rol para eficiencia
        if ($user->role === 'Cliente') {
            Auth::user()->load('cliente');
        } elseif ($user->role === 'Veterinario') {
            Auth::user()->load('veterinarian');
        }


        return redirect()->route('profile.edit')->with('status', '¡Perfil actualizado exitosamente!');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        // Esto es crucial: SOLO permitir cambiar contraseña si el usuario tiene un método de autenticación local.
        if ($user->provider !== 'email' && $user->provider !== null) {
            return redirect()->route('profile.edit')->with('error', 'No puedes cambiar la contraseña para una cuenta vinculada a un proveedor externo como Google.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'max:255'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')->with('status', 'password-updated');
    }
}