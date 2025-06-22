<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;

class ProductObserver
{
    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Solo actuar si el stock ha cambiado
        if ($product->isDirty('stock')) {
            // El stock ANTERIOR antes de la actualización
            $originalStock = $product->getOriginal('stock');

            // El stock ACTUAL después de la actualización
            $currentStock = $product->stock;

            // El umbral de stock bajo definido PARA ESTE PRODUCTO
            $minThreshold = $product->min_stock_threshold;

            // Condición para enviar la notificación:
            // 1. El stock actual es MENOR o IGUAL al umbral definido para el producto.
            // 2. El stock ORIGINAL (antes de la actualización) era MAYOR que el umbral.
            //    Esto previene enviar múltiples notificaciones si el stock ya estaba bajo y se sigue editando sin subirlo.
            if (
                $currentStock <= $minThreshold &&
                $originalStock > $minThreshold
            ) {
                // Obtén todos los usuarios que sean administradores
                $adminUsers = User::where('role', 'admin')->get();

                foreach ($adminUsers as $admin) {
                    $admin->notify(new LowStockNotification($product));
                }
            }
        }
    }
}