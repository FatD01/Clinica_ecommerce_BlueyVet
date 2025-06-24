<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'is_active'];

    // Relación Muchos a Muchos con servicios
    // ¡NUEVA! Relación con Servicios
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_specialty', 'specialty_id', 'service_id');
    }

    // Relación Muchos a Muchos con veterinarios
    public function veterinarians()
    {
        return $this->belongsToMany(Veterinarian::class, 'specialty_veterinarian');
    }
}