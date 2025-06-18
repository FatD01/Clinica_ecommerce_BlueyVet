<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Veterinarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'veterinarians'; // Asegúrate que coincida con el nombre real de la tabla
    
    protected $fillable = [
        'user_id',
        'license_number',
        'specialty',
        'phone',
        'address',
        'bio',
    ];

    // A veterinarian belongs to a user account
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    // A veterinarian can have many medical records
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Relación con las citas.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'veterinarian_id');
    }

    public function scheduleBlocks(): HasMany
    {
        return $this->hasMany(ScheduleBlock::class);
    }

}
