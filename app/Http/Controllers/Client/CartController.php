<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Promotion; // Es buena práctica importar todas las clases de modelos que uses
use Carbon\Carbon; // Ya estaba, solo confirmando

class CartController extends Controller
{
    /**
     * Revalida y recalcula el carrito de la sesión.
     * Es llamado por otros métodos para asegurar la consistencia.
     *
     * @param array $cart El carrito actual de la sesión.
     * @return array Contiene el carrito actualizado, el total y el conteo de ítems.
     */
    public function revalidateAndCalculateCart(array $cart)
    {
        $updatedCart = [];
        $total = 0;
        $cartCount = 0; // Para el badge del carrito

        foreach ($cart as $productId => $item) {
            $productFromDb = Product::find($productId);

            // Si el producto no existe en la BD o ya no tiene stock, lo omitimos/eliminamos
            if (!$productFromDb || $productFromDb->stock <= 0) {
                continue; // El producto será eliminado del carrito actualizado
            }

            // Asegurarse de que la cantidad en el carrito no exceda el stock actual
            $quantityInCart = min($item['quantity'], $productFromDb->stock);
            if ($quantityInCart <= 0) { // Si por alguna razón la cantidad se vuelve 0 o menos
                continue;
            }

            // 1. Obtener TODAS las promociones activas para este producto
            $activePromotions = $productFromDb->getActivePromotions();

            // 2. Aplicar TODAS las promociones y obtener el resultado combinado
            $promotionResult = $productFromDb->applyPromotions(
                $productFromDb->price,
                $quantityInCart,
                $activePromotions
            );

            // Extraer los resultados de la aplicación de promociones
            $effectivePricePerUnit = $promotionResult['final_price_per_unit'];
            $giftQuantity = $promotionResult['gift_quantity'];
            $appliedPromotionTitles = $promotionResult['applied_promotion_titles']; // Ahora es un array

            // Almacenar el ítem con la información actualizada y la cantidad de regalo
            $updatedCart[$productId] = [
                'id' => $productFromDb->id,
                'name' => $productFromDb->name,
                'price' => $productFromDb->price, // Precio original del producto
                'discounted_price' => $effectivePricePerUnit, // Precio final por unidad después de todos los descuentos
                'promotion_titles' => $appliedPromotionTitles, // ¡Ahora es un array de títulos!
                'image' => $productFromDb->image,
                'quantity' => $quantityInCart, // Cantidad que el usuario *paga*
                'gift_quantity' => $giftQuantity, // Cantidad de regalo total
                'stock' => $productFromDb->stock,
                'effective_price_per_unit' => $effectivePricePerUnit, // Precio que se usa para el cálculo del subtotal del item
            ];

            // Cálculo del total general del carrito
            // Para 'buy_x_get_y', solo se cobra la cantidad pagada ('quantity'), la cantidad de regalo es gratuita.
            $total += $effectivePricePerUnit * $quantityInCart;
            $cartCount++; // Solo contamos los productos que realmente están en el carrito después de la revalidación
        }

        session()->put('cart', $updatedCart); // Guarda el carrito revalidado en la sesión
        return ['cart' => $updatedCart, 'total' => $total, 'cart_count' => $cartCount];
    }

    /**
     * Agrega un producto al carrito o actualiza su cantidad.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $cart = session()->get('cart', []);
        $quantityToAdd = $request->input('quantity', 1);

        if ($quantityToAdd <= 0) {
            return response()->json(['success' => false, 'message' => 'La cantidad a agregar debe ser al menos 1.']);
        }

        $currentQtyInCart = isset($cart[$productId]) ? $cart[$productId]['quantity'] : 0;
        $newQty = $currentQtyInCart + $quantityToAdd;

        if ($newQty > $product->stock) {
            return response()->json(['success' => false, 'message' => 'No se puede agregar más de este producto. Stock disponible: ' . $product->stock]);
        }

        // Obtener TODAS las promociones activas para el producto
        $activePromotions = $product->getActivePromotions();

        // Aplicar TODAS las promociones para la nueva cantidad
        $promotionResult = $product->applyPromotions(
            $product->price,
            $newQty, // Usar la nueva cantidad para el cálculo de promociones
            $activePromotions
        );

        $effectivePricePerUnit = $promotionResult['final_price_per_unit'];
        $giftQuantity = $promotionResult['gift_quantity'];
        $appliedPromotionTitles = $promotionResult['applied_promotion_titles']; // Array de títulos

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $newQty;
            // Actualizar todos los campos por si la promoción cambió
            $cart[$productId]['price'] = $product->price;
            $cart[$productId]['discounted_price'] = $effectivePricePerUnit;
            $cart[$productId]['promotion_titles'] = $appliedPromotionTitles; // Guardar el array
            $cart[$productId]['gift_quantity'] = $giftQuantity;
            $cart[$productId]['effective_price_per_unit'] = $effectivePricePerUnit;
            $cart[$productId]['stock'] = $product->stock; // Actualizar stock también
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'discounted_price' => $effectivePricePerUnit,
                'promotion_titles' => $appliedPromotionTitles, // Guardar el array
                'image' => $product->image,
                'quantity' => $newQty,
                'gift_quantity' => $giftQuantity,
                'stock' => $product->stock,
                'effective_price_per_unit' => $effectivePricePerUnit,
            ];
        }

        session()->put('cart', $cart);
        
        // Revalidar y recalcular todo el carrito después de agregar/actualizar un ítem
        $revalidatedData = $this->revalidateAndCalculateCart(session()->get('cart', []));

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_count' => $revalidatedData['cart_count'], // Usar el conteo revalidado
        ]);
    }

    /**
     * Actualiza la cantidad de un producto en el carrito (incrementa/decrementa).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $action = $request->input('action');
        $cart = session()->get('cart', []);

        if (!isset($cart[$product->id])) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado en el carrito']);
        }

        $currentQty = $cart[$product->id]['quantity'];
        $productData = Product::findOrFail($product->id); // Usamos productData para asegurar que es la última versión de DB
        $stock = $productData->stock;

        $newQty = $currentQty;
        if ($action === 'increase') {
            if ($currentQty < $stock) {
                $newQty++;
            } else {
                return response()->json(['success' => false, 'message' => 'No se puede aumentar más la cantidad. Stock disponible: ' . $stock]);
            }
        } elseif ($action === 'decrease') {
            if ($currentQty > 1) {
                $newQty--;
            } else {
                return response()->json(['success' => false, 'message' => 'La cantidad mínima es 1.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Acción no válida.']);
        }

        // Actualizar la cantidad en el ítem del carrito
        $cart[$product->id]['quantity'] = $newQty;

        // Obtener TODAS las promociones activas para el producto
        $activePromotions = $productData->getActivePromotions();

        // Aplicar TODAS las promociones para la nueva cantidad
        $promotionResult = $productData->applyPromotions(
            $productData->price,
            $newQty, // Usar la nueva cantidad para el cálculo de promociones
            $activePromotions
        );
        
        $effectivePricePerUnit = $promotionResult['final_price_per_unit'];
        $giftQuantity = $promotionResult['gift_quantity'];
        $appliedPromotionTitles = $promotionResult['applied_promotion_titles']; // Array de títulos

        // Actualizar todos los campos del carrito con la nueva información de promoción
        $cart[$product->id]['price'] = $productData->price;
        $cart[$product->id]['discounted_price'] = $effectivePricePerUnit;
        $cart[$product->id]['promotion_titles'] = $appliedPromotionTitles; // Guardar el array
        $cart[$product->id]['gift_quantity'] = $giftQuantity;
        $cart[$product->id]['effective_price_per_unit'] = $effectivePricePerUnit;
        $cart[$product->id]['stock'] = $productData->stock;

        session()->put('cart', $cart); // Guarda el carrito actualizado en la sesión
        
        // Revalidar y recalcular todo el carrito después de agregar/actualizar un ítem
        $revalidatedData = $this->revalidateAndCalculateCart($cart);

        $itemPriceEffective = $revalidatedData['cart'][$product->id]['effective_price_per_unit'];
        $itemQuantity = $revalidatedData['cart'][$product->id]['quantity'];
        $itemGiftQuantity = $revalidatedData['cart'][$product->id]['gift_quantity'];
        
        return response()->json([
            'success' => true,
            'newQty' => $itemQuantity,
            'newSubtotal' => number_format($itemPriceEffective * $itemQuantity, 2),
            'total' => number_format($revalidatedData['total'], 2),
            'promotion_titles' => $appliedPromotionTitles, // Devolver el array de títulos al front-end
            'item_price_effective' => number_format($itemPriceEffective, 2),
            'item_gift_quantity' => $itemGiftQuantity,
            'cart_count' => $revalidatedData['cart_count'],
            'stock' => $productData->stock
        ]);
    }

    /**
     * Elimina un producto del carrito.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Product $product)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session()->put('cart', $cart);
        }

        // Revalidar y recalcular todo el carrito después de eliminar un ítem
        $revalidatedData = $this->revalidateAndCalculateCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'total' => number_format($revalidatedData['total'], 2),
            'cart_count' => $revalidatedData['cart_count'],
        ]);
    }

    /**
     * Obtiene el HTML del componente flotante del carrito.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartComponent()
    {
        // 1. Revalida el carrito de la sesión y calcula el total
        $revalidatedData = $this->revalidateAndCalculateCart(session()->get('cart', []));
        
        // 2. Pasa el carrito revalidado y el total calculado a la vista
        $cart = $revalidatedData['cart'];
        $total = $revalidatedData['total'];
        
        // 3. Renderiza la vista con los datos ya listos
        $html = view('components.cart-floating', compact('cart', 'total'))->render();
        return response()->json(['html' => $html]);
    }
}