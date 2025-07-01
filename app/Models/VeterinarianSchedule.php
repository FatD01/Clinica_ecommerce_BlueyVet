<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class VeterinarianSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'veterinarian_id',
        'day_of_week',
        'start_time',
        'end_time',
        'color',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',   
    ];

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class);
    }
}
