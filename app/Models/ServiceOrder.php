<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'amount',
        'currency',
        'paypal_order_id',
        'payer_id',        // Asumiendo que quieres almacenar el ID del pagador de PayPal
        'status',          // Mantén solo una entrada 'status'
        'payment_details', // Para almacenar la respuesta JSON completa de PayPal
    ];

    protected $casts = [
        'payment_details' => 'array', // Convierte payment_details a un array automáticamente
    ];

    // Define la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define la relación con el modelo Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}