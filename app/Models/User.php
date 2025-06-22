<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute; // ¡Importante! Necesario para los accessors
use Illuminate\Support\Facades\Auth; // Importante para Auth::check() dentro del accessor

use Illuminate\Database\Eloquent\Relations\HasMany;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
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
        'provider',
        "role",
        'google_id',
        'email_verified_at',
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

    // --- Relaciones ---
    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class);
    }

    public function veterinarian(): HasOne
    {
        return $this->hasOne(Veterinarian::class);
    }

    // --- Accessor para determinar si el perfil del usuario necesita ser completado ---
    /**
     * Determine if the user's profile needs completion.
     */
    protected function needsProfileCompletion(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Si el usuario no está autenticado, no tiene sentido mostrar el punto.
                if (!Auth::check()) {
                    return false;
                }

                // Asegúrate de que el objeto user sea el autenticado actual.
                // Aunque $this ya es el usuario autenticado en el contexto de un accessor,
                // usar Auth::user() aquí es una redundancia segura si el accessor fuera llamado fuera de la instancia del usuario autenticado.
                // Para este caso, $this es suficiente y más directo.
                $user = $this;

                // Solo aplicamos esta lógica si el usuario tiene el rol 'Cliente'
                if ($user->role === 'Cliente') {
                    // Cargar la relación 'cliente' si no está ya cargada.
                    // Esto es CRUCIAL para evitar errores si 'cliente' no se ha cargado.
                    if (!$user->relationLoaded('cliente')) {
                        $user->load('cliente');
                    }

                    // Si el usuario es un Cliente pero NO tiene un registro de cliente asociado,
                    // el perfil está incompleto. Esto puede pasar si el registro de cliente
                    // no se creó automáticamente al registrarse.
                    if (is_null($user->cliente)) {
                        return true;
                    }

                    // Comprobaciones para los campos obligatorios del cliente: teléfono y dirección
                    // 'empty()' es bueno porque comprueba NULL, cadenas vacías y 0.
                    if (empty($user->cliente->telefono) || empty($user->cliente->direccion)) {
                        return true;
                    }

                    // Comprobación específica para el apellido si NO es una cuenta de Google
                    // Si el usuario se registró con Google, no le exigimos el apellido para completar el perfil.
                    if ($user->provider !== 'google') {
                        if (empty($user->cliente->apellido)) {
                            return true;
                        }
                    }

                    // Opcional: Si el email también debe estar verificado para considerar el perfil completo
                    // if (!$user->hasVerifiedEmail()) {
                    //     return true;
                    // }

                    // Si todas las condiciones anteriores son falsas, el perfil del cliente se considera completo.
                    return false;
                }

                // Para cualquier otro rol (ej. 'admin', 'veterinarian') o si el rol no es 'Cliente',
                // el punto de "perfil incompleto de cliente" no aplica.
                return false;
            },
        );
    }
    
    public function orders(): HasMany // Define la relación para los pedidos
    {
        return $this->hasMany(Order::class);
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