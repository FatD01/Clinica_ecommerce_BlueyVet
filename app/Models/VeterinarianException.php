<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VeterinarianException extends Model
{
    protected $fillable = [
        'veterinarian_id',
        'date',
        'reason',
    ];
}
