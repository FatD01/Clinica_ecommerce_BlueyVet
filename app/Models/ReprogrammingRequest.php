<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa SoftDeletes

class ReprogrammingRequest extends Model
{
    use HasFactory, SoftDeletes; // Usa el trait SoftDeletes

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reprogramming_requests'; // Nombre de la tabla explícito

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'appointment_id',
        'client_id',
        'veterinarian_id',
        'requester_type',
        'requester_user_id',
        'proposed_start_date_time',
        'proposed_end_date_time',
        'reprogramming_reason',
        'client_confirmed',
        'client_confirmed_at',
        'veterinarian_confirmed',
        'veterinarian_confirmed_at',
        'status',
        'admin_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'proposed_start_date_time' => 'datetime',
        'proposed_end_date_time' => 'datetime',
        'client_confirmed' => 'boolean',
        'client_confirmed_at' => 'datetime',
        'veterinarian_confirmed' => 'boolean',
        'veterinarian_confirmed_at' => 'datetime',
    ];

    // Relaciones Eloquent

    /**
     * Get the appointment that owns the reprogramming request.
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the client associated with the reprogramming request.
     */
    public function client()
    {
        return $this->belongsTo(Cliente::class); // Asegúrate que 'Cliente' es el nombre de tu modelo de clientes
    }

    /**
     * Get the veterinarian associated with the reprogramming request.
     */
    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class);
    }

    /**
     * Get the user who made the reprogramming request.
     */
    public function requesterUser()
    {
        return $this->belongsTo(User::class, 'requester_user_id'); // Específicamos la FK si no es la convención 'user_id'
    }
}