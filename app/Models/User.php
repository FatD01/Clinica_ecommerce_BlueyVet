<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\HasOne;

// --- INICIO: Importaciones necesarias para Filament ---
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
// --- FIN: Importaciones necesarias para Filament ---

// --- INICIO: Se añade 'implements FilamentUser' ---
class User extends Authenticatable implements FilamentUser
// --- FIN: Se añade 'implements FilamentUser' ---
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        "role", // Asegúrate de que "role" esté aquí
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- INICIO: Relaciones que ya tenías ---
    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class);
    }

    public function veterinarian(): HasOne
    {
        return $this->hasOne(Veterinarian::class);
    }
    // --- FIN: Relaciones que ya tenías ---

    // --- INICIO: Método requerido por FilamentUser ---
    /**
     * Determina si el usuario puede acceder a un panel específico de Filament.
     *
     * @param  \Filament\Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Asumiendo que tu panel administrativo es el que tiene ID 'dashboard'
        // Puedes verificar el ID del panel en app/Providers/Filament/DashboardPanelProvider.php
        if ($panel->getId() === 'dashboard') {
            // Solo los usuarios con rol 'admin' o 'veterinario' pueden acceder a este panel.
            return $this->role === 'admin';
        }

        // Si tienes otros paneles de Filament y quieres que otros roles accedan,
        // puedes añadir más lógica aquí, o simplemente retornar true para permitir acceso
        // a cualquier otro panel por cualquier usuario.
        return true;
    }
    // --- FIN: Método requerido por FilamentUser ---
}