<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\VeterinarianScheduleProposal;

class Veterinarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'license_number',
        'specialty',
        'phone',
        'address',
        'bio',
    ];

    // A veterinarian belongs to a user account
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // A veterinarian can have many medical records
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(VeterinarianSchedule::class);
    }

    /**
     * Obtiene las excepciones de horario para fechas específicas (días libres, turnos extra, etc., gestionados por el administrador).
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(VeterinarianException::class);
    }

    /**
     * Obtiene las propuestas de horario enviadas por el veterinario (pendientes de revisión por el administrador).
     */
    public function scheduleProposals(): HasMany
    {
        return $this->hasMany(VeterinarianScheduleProposal::class);
    }

    // Si tuvieras un modelo para citas (appointments), podrías añadir:
    // public function appointments(): HasMany
    // {
    //     return $this->hasMany(Appointment::class);
    // }
}