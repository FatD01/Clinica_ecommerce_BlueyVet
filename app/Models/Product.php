<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Observers\ProductObserver; // <<<<<<<< AÑADIR ESTA LÍNEA

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id',
        'image',
        'min_stock_threshold', // <<<<<<<< AÑADIR ESTA LÍNEA
    ];

    // <<<<<<<< AÑADIR ESTE MÉTODO COMPLETO (Método boot() para registrar el observador)
    protected static function boot()
    {
        parent::boot();

        static::observe(ProductObserver::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'product_promotion')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Obtiene TODAS las promociones activas y aplicables para este producto.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActivePromotions()
    {
        $now = Carbon::now();

        return $this->promotions->filter(function ($promotion) use ($now) {
            return $promotion->is_enabled &&
                ($promotion->start_date === null || $promotion->start_date <= $now) &&
                ($promotion->end_date === null || $promotion->end_date >= $now);
        });
    }

    /**
     * Aplica todas las promociones dadas a un precio y cantidad,
     * retornando el precio final del item y la cantidad de regalo total.
     *
     * @param float $basePrice El precio base del producto.
     * @param int $quantity La cantidad comprada del producto.
     * @param \Illuminate\Database\Eloquent\Collection $promotions Una colección de promociones activas.
     * @return array ['final_price_per_unit' => float, 'gift_quantity' => int, 'applied_promotion_titles' => array]
     */
    public function applyPromotions(float $basePrice, int $quantity, $promotions)
    {
        $currentPrice = $basePrice;
        $totalGiftQuantity = 0;
        $appliedPromotionTitles = [];

        // Asegurarse de que $promotions sea una colección de Eloquent
        if (!($promotions instanceof \Illuminate\Database\Eloquent\Collection)) {
            $promotions = collect($promotions);
        }

        // Extraer los títulos de todas las promociones aplicables para retornarlos
        $appliedPromotionTitles = $promotions->pluck('title')->toArray();

        // 1. Filtrar y ordenar promociones de descuento fijo (los mayores primero)
        $fixedAmountPromotions = $promotions->filter(function ($promo) {
            return $promo->discount_type === 'fixed_amount';
        })->sortByDesc('discount_value');

        // 2. Filtrar y ordenar promociones de descuento porcentual (los mayores primero)
        $percentagePromotions = $promotions->filter(function ($promo) {
            return $promo->discount_type === 'percentage';
        })->sortByDesc('discount_value');

        // APLICAR DESCUENTOS DE MONTO FIJO PRIMERO
        foreach ($fixedAmountPromotions as $promotion) {
            $currentPrice -= $promotion->discount_value;
            // Asegurar que el precio nunca sea negativo
            $currentPrice = max(0, $currentPrice);
        }

        // APLICAR DESCUENTOS PORCENTUALES DESPUÉS
        foreach ($percentagePromotions as $promotion) {
            $discountAmount = $currentPrice * ($promotion->discount_value / 100);
            $currentPrice -= $discountAmount;
            // Asegurar que el precio nunca sea negativo
            $currentPrice = max(0, $currentPrice);
        }

        // APLICAR PROMOCIONES "BUY X GET Y" (estas solo afectan la cantidad de regalo, no el precio unitario pagado)
        $buyXGetYPromotions = $promotions->where('discount_type', 'buy_x_get_y');

        foreach ($buyXGetYPromotions as $promotion) {
            if ($promotion->buy_quantity > 0 && $quantity >= $promotion->buy_quantity) {
                $numSets = floor($quantity / $promotion->buy_quantity);
                $totalGiftQuantity += $numSets * $promotion->get_quantity;
            }
        }

        // Limpiar duplicados de títulos si alguna promoción tiene el mismo título
        $appliedPromotionTitles = array_values(array_unique($appliedPromotionTitles));

        return [
            'final_price_per_unit' => $currentPrice,
            'gift_quantity' => $totalGiftQuantity,
            'applied_promotion_titles' => $appliedPromotionTitles,
        ];
    }

    // <<<<<<<< AÑADIR ESTE MÉTODO COMPLETO (Scope para consultar productos con bajo stock)
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock_threshold');
    }
}