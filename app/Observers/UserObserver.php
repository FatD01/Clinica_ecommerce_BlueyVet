<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Veterinarian; // Asegúrate de importar el modelo Veterinarian
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     * Cuando un usuario es creado.
     */
    public function created(User $user): void
    {
        // Si el nuevo usuario tiene el rol 'veterinario', crea un registro de Veterinarian
       Log::info("UserObserver: Evento 'created' disparado para User ID: {$user->id}, Nombre: {$user->name}, Rol: {$user->role}");

        if ($user->role === 'Veterinario') {
            Log::info("UserObserver: Rol es 'veterinario'. Procesando creación de Veterinarian para User ID: {$user->id}");

            $veterinarian = $user->veterinarian()->withTrashed()->first();

            if (!$veterinarian) {
                Veterinarian::create([
                    'user_id' => $user->id,
                    'license_number' => null,
                    'specialty' => null,
                    'phone' => null,
                    'address' => null,
                    'bio' => null,
                ]);
                Log::info("UserObserver: CREADO nuevo registro Veterinarian para User ID: {$user->id}");
            } else {
                if ($veterinarian->trashed()) {
                    $veterinarian->restore();
                    Log::info("UserObserver: RESTAURADO registro Veterinarian existente para User ID: {$user->id}");
                } else {
                    Log::info("UserObserver: REGISTRO Veterinarian ya existe (activo) para User ID: {$user->id}. No se crea duplicado.");
                }
            }
        } else {
            Log::info("UserObserver: Rol NO es 'veterinario'. No se crea registro Veterinarian para User ID: {$user->id}");
        }
    }


    
    /**
     * Handle the User "updated" event.
     * Cuando un usuario es actualizado (especialmente su rol).
     */
   public function updated(User $user): void
    {
        // Si el rol del usuario cambió a 'veterinario'
        if ($user->isDirty('role') && $user->role === 'veterinario') {
            $veterinarian = $user->veterinarian()->withTrashed()->first(); // Busca incluso soft-deleted

            if (!$veterinarian) { // Si NO existe un registro de Veterinarian asociado
                Veterinarian::create([
                    'user_id' => $user->id,
                    'license_number' => null,
                    'specialty' => null,
                    'phone' => null,
                    'address' => null,
                    'bio' => null,
                ]);
                Log::info("UserObserver: Nuevo registro Veterinarian creado (por cambio de rol) para User ID: {$user->id}");
            } elseif ($veterinarian->trashed()) { // Si existía pero estaba soft-deleted
                $veterinarian->restore();
                Log::info("UserObserver: Registro Veterinarian existente restaurado (por cambio de rol) para User ID: {$user->id}");
            }
        }
        // Si el rol del usuario cambió DE 'veterinario' a otra cosa, soft-delete su registro de veterinario
        elseif ($user->isDirty('role') && $user->getOriginal('role') === 'veterinario' && $user->role !== 'veterinario') {
            if ($user->veterinarian) { // Si tiene un registro Veterinarian activo
                $user->veterinarian->delete(); // Soft delete
                Log::info("UserObserver: Soft-deleted registro Veterinarian para User ID: {$user->id}");
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