<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Si usaste softDeletes en la migración

class ServiceOrder extends Model
{
    use HasFactory, SoftDeletes; // Agrega SoftDeletes si lo usaste

    protected $fillable = [
        'user_id' ,
        'service_id',
        'amount',
        'paypal_order_id',
        'status',
        'payment_details',
    ];

    // Definir la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Definir la relación con el modelo Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}