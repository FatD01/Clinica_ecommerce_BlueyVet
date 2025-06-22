<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes', // Según tu diagrama
        'status', // Según tu diagrama
    ];

    // Relación Many-to-Many con promociones
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_service');
    }
}