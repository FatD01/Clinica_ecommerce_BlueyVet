<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeterinarianSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'veterinarian_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    /**
     * Obtiene el veterinario al que pertenece este horario.
     */
    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class);
    }

    /**
     * Atributo accesorio para obtener el nombre del día de la semana.
     */
    public function getDayNameAttribute()
    {
        $days = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];
        return $days[$this->day_of_week] ?? 'Desconocido';
    }
}