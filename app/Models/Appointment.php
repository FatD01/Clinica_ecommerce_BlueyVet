<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mascota_id',
        'veterinarian_id',
        'date',
        'reason',
        'status',
        'service_id',
        'service_order_id', 
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class);
    }

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class);
    }

    public function service(): BelongsTo // Nueva relación para el servicio asociado con la cita
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceOrder(): BelongsTo // Nueva relación para la orden de servicio que pagó esta cita
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }
}