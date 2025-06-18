<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDay extends Model
{
    protected $fillable = [
        'date',
        'reason',
    ];
    protected $casts = [
        'date' => 'date', // Asegura que se maneje como un objeto Carbon de fecha
    ];
}
