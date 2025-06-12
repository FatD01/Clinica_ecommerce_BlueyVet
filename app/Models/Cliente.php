<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nombre',
        'apellido',
        'telefono',
        // 'dni',
        // 'fecha_nacimiento',
        'direccion',
    ];


    // protected $casts = [
    //     'birth_date' => 'date',
    // ];
public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mascotas(): HasMany
    {
        return $this->hasMany(Mascota::class);
    }

    // ¡CAMBIO AQUÍ! Ahora 'purchasedServices' se relaciona con ServiceOrder
    public function purchasedServices(): HasMany
    {
        // Traerá todas las ServiceOrder para este cliente donde el estado sea 'COMPLETED'
        // Asegúrate de usar el 'status' y el valor ('COMPLETED') que tu integración PayPal usa para pagos exitosos.
        return $this->hasMany(ServiceOrder::class, 'user_id', 'user_id')
                    ->where('status', 'COMPLETED'); // <-- AJUSTA 'COMPLETED' al estado real de tu PayPal para un pago exitoso
    }

    // Si tu tabla Cliente tiene 'user_id' para relacionarse con el modelo User,
    // asegúrate de que esté en los fillable si es que lo asignas directamente.
    // También asegúrate de que 'user_id' es una foreign key en tu migración de 'clientes'.
}