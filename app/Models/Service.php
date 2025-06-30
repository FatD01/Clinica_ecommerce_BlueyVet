<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes', // Según tu diagrama
        'status', // Según tu diagrama
        'image_url',
    ];

    // Relación Many-to-Many con promociones
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_service');
    }

    public function specialties(): BelongsToMany
    {
        // 'service_specialty' es el nombre de tu tabla pivote
        // 'service_id' es la FK de Service en la tabla pivote
        // 'specialty_id' es la FK de Specialty en la tabla pivote
        return $this->belongsToMany(Specialty::class, 'service_specialty', 'service_id', 'specialty_id');
    }

    public function getAssociatedVeterinariansAttribute(): Collection
    {
        // Obtener las IDs de las especialidades de este servicio
        $requiredSpecialtyIds = $this->specialties->pluck('id')->toArray();

        // Si el servicio no tiene especialidades asociadas, devuelve una colección vacía
        if (empty($requiredSpecialtyIds)) {
            return collect();
        }

        // Buscar veterinarios que tengan AL MENOS UNA de las especialidades requeridas
        // y precargar sus relaciones 'user' y 'specialties' para evitar N+1 queries en la vista
        return Veterinarian::whereHas('specialties', function ($query) use ($requiredSpecialtyIds) {
            $query->whereIn('specialties.id', $requiredSpecialtyIds);
        })
        ->with(['user', 'specialties']) // Precarga las relaciones 'user' y 'specialties' para cada veterinario encontrado
        ->get();
    }

    /**
     * Método para determinar si este servicio está "disponible" (tiene veterinarios con sus especialidades).
     * Esto es parte de la lógica de "Servicio Pronto Disponible".
     */
    public function hasAvailableVeterinarians(): bool
    {
        // Primero, obtenemos las IDs de las especialidades que este servicio requiere.
        $requiredSpecialtyIds = $this->specialties()->pluck('id')->toArray();

        // Si el servicio no tiene especialidades asociadas, ¿se considera disponible?
        // Esto es una decisión de negocio. Por ahora, asumamos que necesita al menos una especialidad.
        if (empty($requiredSpecialtyIds)) {
            return false;
        }

        // Busca si existe al menos un veterinario que tenga AL MENOS UNA de las especialidades requeridas
        // y que esté activo (si tienes un campo 'is_active' en Veterinarian, sería buena idea usarlo).
        return Veterinarian::whereHas('specialties', function ($query) use ($requiredSpecialtyIds) {
            $query->whereIn('specialties.id', $requiredSpecialtyIds);
        })
        // ->where('is_active', true) // Si tienes un campo 'is_active' en tu modelo Veterinarian
        ->exists();
    }
    public function getImageUrlAttribute()
    {
        // Aquí puedes poner la lógica para obtener la URL.
        // Por ejemplo, si tienes un campo 'image_filename' y las imágenes están en 'storage/app/public/services/'
        if ($this->image_filename) {
            return asset('storage/services/' . $this->image_filename);
        }

        // O si simplemente quieres un valor por defecto si no hay lógica para una imagen específica
        return asset('img/service-default.jpg');
    }
    

 
}