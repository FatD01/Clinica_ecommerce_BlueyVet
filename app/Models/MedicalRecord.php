<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    // Ensure the model uses the correct table name (plural of snake_case model name by default)
    protected $table = 'medical_records';

    protected $fillable = [
        'mascota_id',
        'veterinarian_id',
        'service_id',
        'consultation_date',
        'reason_for_consultation',
        'diagnosis',
        'treatment',
        'prescription', //
        'observations',//
        'notes',
        'appointment_id', // ¡Asegúrate de que este campo está en tu tabla y en fillable!
        'pfd_file', // esto permitir exportarse, no subir.
    ];

    protected $casts = [
        'consultation_date' => 'datetime',
    ];

    // A medical record belongs to a Mascota
    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class); // Your existing Mascota model
    }

    // A medical record belongs to a Veterinarian
    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class);
    }

    // A medical record can optionally belong to a Service
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class); // Assuming you have a Service model
    }

    public function appointment(): BelongsTo
    {
        // Asegúrate de que App\Models\Appointment es la ruta correcta a tu modelo Appointment
        return $this->belongsTo(Appointment::class);
    }
}