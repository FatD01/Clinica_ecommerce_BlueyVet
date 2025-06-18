<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Session; // ¡MUY IMPORTANTE: Asegúrate de importar Session!

class CartFloating extends Component
{
    // Declara las propiedades públicas que la vista usará
    public $cart;
    public $total;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        // Obtener el carrito de la sesión. Si no existe, inicializarlo como un array vacío.
        $this->cart = Session::get('cart', []);

        // Calcular el total a partir de los ítems del carrito.
        $calculatedTotal = 0;
        foreach ($this->cart as $item) {
            // Usamos 'effective_price_per_unit' si existe, de lo contrario 'price',
            // y aseguramos un fallback a 0 para evitar errores si no están definidos.
            $priceToUse = $item['effective_price_per_unit'] ?? $item['price'] ?? 0;
            $quantityToUse = $item['quantity'] ?? 0;
            $calculatedTotal += ($priceToUse * $quantityToUse);
        }

        // Formatear el total a dos decimales.
        $this->total = number_format($calculatedTotal, 2, '.', '');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // Retorna la vista Blade de tu carrito flotante
        // Las variables $this->cart y $this->total estarán automáticamente disponibles en esta vista.
        return view('components.cart-floating');
    }
}