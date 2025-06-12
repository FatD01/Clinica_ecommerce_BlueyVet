<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeterinarianException extends Model
{
    use HasFactory;

    protected $fillable = [
        'veterinarian_id',
        'date',
        'start_time',
        'end_time',
        'type', // 'available' or 'unavailable'
        'notes',
    ];

    protected $casts = [
        'date' => 'date', // Laravel automáticamente convertirá este campo a un objeto Carbon
    ];

    /**
     * Obtiene el veterinario al que pertenece esta excepción.
     */
    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class);
    }
}