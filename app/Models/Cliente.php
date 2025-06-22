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
        'direccion',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function mascotas(): HasMany
    {
        return $this->hasMany(Mascota::class);
    }
    public function reprogrammingRequests()
    {
        return $this->hasMany(ReprogrammingRequest::class, 'client_id'); // Asegúrate de especificar la FK
    }
}
