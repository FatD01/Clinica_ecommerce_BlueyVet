<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'veterinarian_id',
        'start_time',
        'end_time',
        'is_recurring',
    ];

    protected $casts = [
        'start_time' => 'datetime', // Asegura que se manejen como objetos DateTime
        'end_time' => 'datetime',   // Asegura que se manejen como objetos DateTime
        'is_recurring' => 'boolean', // Asegura que se maneje como booleano
    ];

    /**
     * Define la relación: Un bloque de horario pertenece a un veterinario.
     */
    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class);
    }
}