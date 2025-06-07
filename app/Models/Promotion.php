<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'apply_to',         // Nuevo
        'discount_type',    // Nuevo
        'discount_value',   // Nuevo
        'buy_quantity',     // Nuevo
        'get_quantity',     // Nuevo
        'start_date',
        'end_date',
        'is_enabled',       // Nuevo
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_enabled' => 'boolean',
        // 'discount_value' => 'decimal:2', // Si quieres castear a decimal
    ];

    public function products()
    {
     
        return $this->belongsToMany(Product::class, 'product_promotion');
    }

    public function services()
    {
        // Necesitas una tabla pivote y una relación aquí si las promociones aplican a servicios
        // Ejemplo: return $this->belongsToMany(Service::class, 'promotion_service');
        // Para esto, necesitarías una migración para crear la tabla 'promotion_service'
        return $this->belongsToMany(Service::class, 'promotion_service');
    }

    /**
     * Determina si la promoción está activa por fechas y si está habilitada.
     */
    public function isActive(): bool
    {
        return $this->is_enabled && Carbon::now()->between($this->start_date, $this->end_date);
    }
}