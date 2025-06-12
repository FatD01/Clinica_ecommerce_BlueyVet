<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'price', 'image', 'stock', 'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'product_promotion')
                    ->withTimestamps();
    }

    /**
     * Obtiene TODAS las promociones activas y aplicables para este producto.
     * Retorna una colección de objetos Promotion.
     */
    public function getActivePromotions()
    {
        $now = Carbon::now();
        return $this->promotions()
                    ->where('is_enabled', true) // Asumo que usas 'is_enabled' de tu código
                    ->where(function ($query) use ($now) {
                        $query->whereNull('start_date')
                              ->orWhere('start_date', '<=', $now);
                    })
                    ->where(function ($query) use ($now) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>=', $now);
                    })
                    // Opcional: ordenar por prioridad si tienes un campo 'priority' en la tabla de promociones
                    // ->orderBy('priority', 'desc')
                    ->get(); // ¡Importante: `get()` para obtener la colección de todas!
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

        // Separar promociones por tipo para aplicar en un orden lógico:
        // Sugerencia: Primero descuentos de precio (porcentaje/monto fijo), luego los regalos.
        // Si hay conflictos de orden, esto es una decisión de negocio.
        $percentagePromotions = $promotions->where('discount_type', 'percentage');
        $fixedAmountPromotions = $promotions->where('discount_type', 'fixed_amount');
        $buyXGetYPromotions = $promotions->where('discount_type', 'buy_x_get_y');

        // 1. Aplicar promociones de porcentaje
        foreach ($percentagePromotions as $promotion) {
            $discountAmount = $currentPrice * ($promotion->discount_value / 100);
            $currentPrice -= $discountAmount;
            $appliedPromotionTitles[] = $promotion->title;
        }

        // 2. Aplicar promociones de monto fijo
        foreach ($fixedAmountPromotions as $promotion) {
            $currentPrice -= $promotion->discount_value;
            $appliedPromotionTitles[] = $promotion->title;
        }

        // Asegurarse de que el precio no sea menor que 0 después de todos los descuentos de precio
        if ($currentPrice < 0) {
            $currentPrice = 0;
        }

        // 3. Calcular cantidad de regalo para buy_x_get_y
        // Aquí sumamos los regalos de todas las promociones buy_x_get_y aplicables.
        // Si solo quieres la "mejor" promoción de regalo (la que da más), la lógica cambiaría.
        foreach ($buyXGetYPromotions as $promotion) {
            if ($promotion->buy_quantity > 0) {
                $numSets = floor($quantity / $promotion->buy_quantity);
                $totalGiftQuantity += $numSets * $promotion->get_quantity;
                $appliedPromotionTitles[] = $promotion->title;
            }
        }
        
        // Eliminar duplicados si una promoción ya se añadió antes por su tipo
        $appliedPromotionTitles = array_unique($appliedPromotionTitles);

        return [
            'final_price_per_unit' => $currentPrice, // El precio final por unidad después de descuentos
            'gift_quantity' => $totalGiftQuantity,
            'applied_promotion_titles' => array_values($appliedPromotionTitles), // Re-indexar el array
        ];
    }

    /**
     * NOTA: `getActivePromotion()`, `getDiscountedPrice()`, y `calculateGiftQuantity()`
     * ya no son necesarios directamente para la lógica del carrito,
     * ya que `applyPromotions()` maneja la combinación.
     * Puedes eliminarlos de este archivo si no se usan en ningún otro lugar.
     */
}