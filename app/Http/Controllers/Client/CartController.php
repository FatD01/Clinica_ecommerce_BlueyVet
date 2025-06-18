<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Promotion; // Asegúrate de que esto esté importado
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Importar el Facade de Log

class CartController extends Controller
{
    /**
     * Revalida y recalcula el carrito de la sesión.
     * Carga productos y promociones de forma eficiente (N+1 resuelto).
     *
     * @param array $cart El carrito actual de la sesión.
     * @return array Contiene el carrito actualizado, el total y el conteo de ítems.
     */
    public function revalidateAndCalculateCart(array $cart)
{
    Log::info('PerfLog: revalidateAndCalculateCart - START'); // Log 1

    $productIds = array_keys($cart);

    if (empty($productIds)) {
        Log::info('PerfLog: revalidateAndCalculateCart - No products, returning early. END'); // Log 2
        session()->put('cart', []);
        return ['cart' => [], 'total' => 0, 'cart_count' => 0];
    }

    Log::info('PerfLog: revalidateAndCalculateCart - Before Product Query'); // Log 3
    $productsInDb = Product::whereIn('id', $productIds)
        ->with(['promotions' => function($query) {
            $now = Carbon::now();
            $query->where('is_enabled', true)
                  ->where(function ($q) use ($now) {
                      $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                  })
                  ->where(function ($q) use ($now) {
                      $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                  });
        }])
        ->get()
        ->keyBy('id');
    Log::info('PerfLog: revalidateAndCalculateCart - After Product Query. Products count: ' . $productsInDb->count()); // Log 4

    $updatedCart = [];
    $total = 0;
    $cartCount = 0;

    Log::info('PerfLog: revalidateAndCalculateCart - Before Loop'); // Log 5
    foreach ($cart as $productId => $item) {
        $productFromDb = $productsInDb->get($productId);

        if (!$productFromDb) {
            Log::warning("PerfLog: Product ID {$productId} not found during revalidation.");
            continue;
        }

        $quantityInCart = min($item['quantity'], $productFromDb->stock);
        if ($quantityInCart <= 0) {
            Log::info("PerfLog: Product ID {$productId} removed due to zero stock/quantity.");
            continue;
        }

        // Estas llamadas ya las verificamos y son eficientes si la colección está cargada.
        $activePromotions = $productFromDb->getActivePromotions();
        $promotionResult = $productFromDb->applyPromotions(
            $productFromDb->price,
            $quantityInCart,
            $activePromotions
        );

        $effectivePricePerUnit = $promotionResult['final_price_per_unit'];
        $giftQuantity = $promotionResult['gift_quantity'];
        $appliedPromotionTitles = $promotionResult['applied_promotion_titles'];

        $updatedCart[$productId] = [
            'id' => $productFromDb->id,
            'name' => $productFromDb->name,
            'price' => $productFromDb->price,
            'discounted_price' => $effectivePricePerUnit,
            'promotion_titles' => $appliedPromotionTitles,
            'image' => $productFromDb->image,
            'quantity' => $quantityInCart,
            'gift_quantity' => $giftQuantity,
            'stock' => $productFromDb->stock,
            'effective_price_per_unit' => $effectivePricePerUnit,
        ];

        $total += $effectivePricePerUnit * $quantityInCart;
        $cartCount++;
    }
    Log::info('PerfLog: revalidateAndCalculateCart - After Loop'); // Log 6

    session()->put('cart', $updatedCart);
    Log::info('PerfLog: revalidateAndCalculateCart - After session put. END'); // Log 7
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
         Log::info('DEBUG: CartController@add - Solicitud recibida para producto ID: ' . $productId);
        Log::info('DEBUG: CartController@add - Datos de la solicitud:', $request->all());
        // Optimizacion: Usar with('promotions') si Product tiene esa relacion para evitar N+1 si la necesitas aqui
        // Aunque revalidateAndCalculateCart lo hará de todos modos de forma masiva
        $product = Product::findOrFail($productId); // Solo una consulta para obtener el producto y su stock

        $cart = session()->get('cart', []);
        $quantityToAdd = $request->input('quantity', 1);

        if ($quantityToAdd <= 0) {
            return response()->json(['success' => false, 'message' => 'La cantidad a agregar debe ser al menos 1.']);
        }

        $currentQtyInCart = isset($cart[$productId]) ? $cart[$productId]['quantity'] : 0;
        $newQty = $currentQtyInCart + $quantityToAdd;

        // Validar stock antes de agregar al carrito
        if ($newQty > $product->stock) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede agregar más de este producto. Stock disponible: ' . $product->stock,
                'stock' => $product->stock, // Devuelve el stock actual para el frontend
                'currentQty' => $currentQtyInCart // Devuelve la cantidad actual en carrito
            ]);
        }

        // Solo actualizamos la cantidad en el carrito.
        // Toda la lógica de precios y promociones se manejará en revalidateAndCalculateCart,
        // garantizando que siempre use los datos más recientes y correctos.
        $cart[$productId] = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'image' => $product->image,
            'quantity' => $newQty,
            'stock' => $product->stock, // Almacenamos el stock actual, aunque revalidate... lo revalidará.
            // Los siguientes campos se establecerán con los valores correctos por revalidateAndCalculateCart
            'discounted_price' => $product->price,
            'promotion_titles' => [],
            'gift_quantity' => 0,
            'effective_price_per_unit' => $product->price,
        ];

        session()->put('cart', $cart);

        // Revalidar y recalcular todo el carrito después de agregar/actualizar un ítem
        $revalidatedData = $this->revalidateAndCalculateCart(session()->get('cart', []));

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_count' => $revalidatedData['cart_count'],
            'total' => number_format($revalidatedData['total'], 2), // Incluir total para actualización
            'item_price_effective' => number_format($revalidatedData['cart'][$productId]['effective_price_per_unit'] ?? $product->price, 2), // Precio efectivo del ítem recién añadido
            'newQty' => $revalidatedData['cart'][$productId]['quantity'] ?? 0, // Cantidad final después de revalidación
            'item_gift_quantity' => $revalidatedData['cart'][$productId]['gift_quantity'] ?? 0,
            'promotion_titles' => $revalidatedData['cart'][$productId]['promotion_titles'] ?? [],
        ]);
    }

    /**
     * Actualiza la cantidad de un producto en el carrito (incrementa/decrementa).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product (Inyectado por Route Model Binding)
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
        // OPTIMIZACIÓN: Ya tienes el objeto $product inyectado por Route Model Binding.
        // Accede directamente a $product->stock, evitando una nueva consulta DB.
        $stock = $product->stock;

        $newQty = $currentQty;
        if ($action === 'increase') {
            if ($currentQty < $stock) {
                $newQty++;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede aumentar más la cantidad. Stock disponible: ' . $stock,
                    'stock' => $stock,
                    'newQty' => $currentQty // Devolver la cantidad actual para que el frontend no la cambie incorrectamente
                ]);
            }
        } elseif ($action === 'decrease') {
            if ($currentQty > 1) {
                $newQty--;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'La cantidad mínima es 1.',
                    'stock' => $stock,
                    'newQty' => $currentQty // Devolver la cantidad actual
                ]);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Acción no válida.']);
        }

        // Actualizar la cantidad en el carrito.
        // La revalidación posterior en revalidateAndCalculateCart se encargará de los precios/promociones.
        $cart[$product->id]['quantity'] = $newQty;
        // El stock en el item del carrito es informativo, la fuente de verdad es product->stock.
        $cart[$product->id]['stock'] = $stock; 

        session()->put('cart', $cart);

        // Revalidar y recalcular todo el carrito después de actualizar un ítem
        $revalidatedData = $this->revalidateAndCalculateCart($cart);

        // Asegúrate de que el producto todavía existe en el carrito revalidado
        if (!isset($revalidatedData['cart'][$product->id])) {
             // Esto podría pasar si el stock se agotó durante la revalidación y el producto fue eliminado.
             return response()->json([
                'success' => false,
                'message' => 'El producto fue eliminado del carrito debido a stock insuficiente.',
                'total' => number_format($revalidatedData['total'], 2),
                'cart_count' => $revalidatedData['cart_count']
             ]);
        }

        $itemPriceEffective = $revalidatedData['cart'][$product->id]['effective_price_per_unit'];
        $itemQuantity = $revalidatedData['cart'][$product->id]['quantity'];
        $itemGiftQuantity = $revalidatedData['cart'][$product->id]['gift_quantity'];
        $appliedPromotionTitles = $revalidatedData['cart'][$product->id]['promotion_titles'];


        return response()->json([
            'success' => true,
            'newQty' => $itemQuantity,
            'newSubtotal' => number_format($itemPriceEffective * $itemQuantity, 2),
            'total' => number_format($revalidatedData['total'], 2),
            'promotion_titles' => $appliedPromotionTitles,
            'item_price_effective' => number_format($itemPriceEffective, 2),
            'item_gift_quantity' => $itemGiftQuantity,
            'cart_count' => $revalidatedData['cart_count'],
            'stock' => $stock // El stock se debe tomar del objeto $product inyectado
        ]);
    }

    /**
     * Elimina un producto del carrito.
     *
     * @param \App\Models\Product $product (Inyectado por Route Model Binding)
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