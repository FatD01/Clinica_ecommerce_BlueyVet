<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\HasOne;


use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;


class User extends Authenticatable implements FilamentUser

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
         'google_id', // ¡Añade esta línea!
        'email_verified_at', // Añade esta línea si la manejas con Google
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
   

    /**
     * Determina si el usuario puede acceder a un panel específico de Filament.
     *
     * @param  \Filament\Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
    
        if ($panel->getId() === 'dashboard') {
            return $this->role === 'admin';
        }

        
        return true;
    }

}