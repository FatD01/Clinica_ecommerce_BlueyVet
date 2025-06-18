<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mascota extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'name',
        'species',
        'race',
        'weight',
        'birth_date',
        'allergies',
        'image'
     // Este campo no es necesario si usas MediaLibrary correctamente
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relación con Citas (appointments)
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'mascota_id');
    }


    /**
     * Relación con Historial Médico
     */
    public function registrosMedicos(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'mascota_id');
    }


    // public function historialMedico(): HasMany
    // {
    //     return $this->hasMany(HistorialMedico::class);
    // }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars') // Nombre de la colección
            ->singleFile() // Si solo quieres una foto de avatar
            ->withResponsiveImages(); // Opcional: genera imágenes responsivas
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10);
    }
}