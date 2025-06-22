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


    // Relaciones existentes
    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class, 'veterinarian_id'); //pucha oeeeee- Conversa urgente  |#conversaurgente aguanta voy a ver si le pongo veterinarian a lo que hice pa ver si da | ya ya, que habrás hecho kjajkajkjka| adaptalo nomás sino no va a dar nada y estaremos perdiendo tiempo
    }

    public function service(): BelongsTo // Nueva relación para el servicio asociado con la cita
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function serviceOrder(): BelongsTo // Nueva relación para la orden de servicio que pagó esta cita
    {
        return $this->belongsTo(ServiceOrder::class);  
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'appointment_id'); //oe, lo que estaba tampoco cambies, solo agrega | se cayó esta parte en el sistema     |que no da es decir | y eso tiene logica con paypal y google| |osea si pero adaptalo pues, seguro tu modelo está en español y aquí estamos haciendo casi todo en inglés, fijate en los modelos, no tenemos veterinario sino veterinarian
    }
    public function reprogrammingRequests()
    {
        return $this->hasMany(ReprogrammingRequest::class);
    }
}