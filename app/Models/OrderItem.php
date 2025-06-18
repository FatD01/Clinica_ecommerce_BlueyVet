<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'quantity',
        'price',
    ];

    /**
     * Get the order that owns the order item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with the order item.
     * Note: This assumes a 'Product' model exists.
     * If 'product_id' can be null or doesn't link to a 'Product' model,
     * you might adjust this relation or handle its absence.
     */
    public function product()
    {
        return $this->belongsTo(Product::class); // Asegúrate de que tu modelo Product exista
    }
}