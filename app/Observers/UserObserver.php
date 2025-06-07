<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Veterinarian; // Asegúrate de importar el modelo Veterinarian

class UserObserver
{
    /**
     * Handle the User "created" event.
     * Cuando un usuario es creado.
     */
    public function created(User $user): void
    {
        // Si el nuevo usuario tiene el rol 'veterinario', crea un registro de Veterinarian
        if ($user->role === 'veterinario') {
            Veterinarian::create([
                'user_id' => $user->id,
                // Puedes establecer valores por defecto o dejarlos nulos
                'license_number' => null, // O un valor por defecto si lo hay
                'specialty' => null,
                'phone' => null,
                'address' => null,
                'bio' => null,
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     * Cuando un usuario es actualizado (especialmente su rol).
     */
    public function updated(User $user): void
    {
        // Si el rol del usuario cambió Y ahora es 'veterinario' Y aún no tiene un registro de veterinario (activo o soft-deleted)
        // Usamos ->withTrashed() para buscar si ya existe un registro de veterinario soft-deleted para este usuario.
        if ($user->isDirty('role') && $user->role === 'veterinario') {
            // Comprobamos si ya existe un registro de Veterinarian (activo o soft-deleted) para este usuario
            if (!$user->veterinarian()->withTrashed()->first()) {
                Veterinarian::create([
                    'user_id' => $user->id,
                ]);
            }
        }
        // Si el rol del usuario cambió DE 'veterinario' a otra cosa, "elimina" suavemente su registro de veterinario
        elseif ($user->isDirty('role') && $user->getOriginal('role') === 'veterinario' && $user->role !== 'veterinario') {
            if ($user->veterinarian) { // Solo si tiene un registro de veterinario activo
                $user->veterinarian->delete(); // Esto hace un soft delete
            }
        }
    }

    /**
     * Handle the User "deleted" event (cuando un usuario es soft-deleted).
     */
    public function deleted(User $user): void
    {
        // Cuando un usuario se "elimina" (soft delete), también "elimina" suavemente su registro de veterinario.
        if ($user->veterinarian) { // Asegura que existe el registro de veterinario (no soft-deleted)
            $user->veterinarian->delete();
        }
    }

    /**
     * Handle the User "restored" event (cuando un usuario es restaurado).
     */
    public function restored(User $user): void
    {
        // Si el usuario es restaurado, busca su registro de veterinario soft-deleted y lo restaura.
        // Asegúrate de que solo intentas restaurar si realmente estaba soft-deleted.
        if ($user->veterinarian()->onlyTrashed()->first()) {
            $user->veterinarian()->restore();
        }
    }
}