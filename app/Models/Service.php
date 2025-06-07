<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes', // Según tu diagrama
        'status', // Según tu diagrama
    ];
//holaefea|holaemo good.|es god|iba a crear mascotas ahorita j ||| yay, deja repaso, no me acuerdo ya jsajd.|okkk
    // Relación Many-to-Many con promociones
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_service');
    }
}