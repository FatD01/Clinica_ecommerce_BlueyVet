<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VeterinarianScheduleProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'veterinarian_id',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'type',
        'status',
        'reason',
    ];

    protected $casts = [
        'date' => 'date', // Convierte el campo 'date' a un objeto Carbon
    ];

    /**
     * Obtiene el veterinario que hizo esta propuesta.
     */
    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class);
    }

    /**
     * Atributo accesorio para obtener el nombre del día de la semana para propuestas recurrentes.
     *
     * @return string|null
     */
    public function getDayNameAttribute(): ?string
    {
        if ($this->day_of_week !== null) {
            $days = [
                0 => 'Domingo',
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
            ];
            return $days[$this->day_of_week] ?? null;
        }
        return null;
    }
}