<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    // Campos que pueden ser llenados masivamente
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'read_at',
    ];

    // Casting para el campo read_at, para que sea un objeto Carbon (fecha)
    protected $casts = [
        'read_at' => 'datetime',
    ];
}