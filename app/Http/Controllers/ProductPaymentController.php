<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Importa las clases de la SDK de PayPal que ya usas
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalHttp\HttpException;
use App\Http\Controllers\Client\CartController; // Tu CartController personalizado

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProductOrderConfirmationMail; // Asegúrate de que esta sea la que se usa
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\ExchangeRateService; // Tu servicio para tasas de cambio

// Importa tus nuevos modelos de Order y OrderItem
use App\Models\Order;
use App\Models\OrderItem;
// Si necesitas Product para decrementar stock
use App\Models\Product; // Asegúrate de tener este modelo si lo usas

class ProductPaymentController extends Controller
{
    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * Helper para inicializar el cliente de PayPal SDK.
     * Copiado directamente de tu PaymentController.
     */
    private function client()
    {
        $clientId = config('services.paypal.mode') === 'sandbox'
            ? config('services.paypal.sandbox.client_id')
            : config('services.paypal.live.client_id');

        $clientSecret = config('services.paypal.mode') === 'sandbox'
            ? config('services.paypal.sandbox.client_secret')
            : config('services.paypal.live.client_secret');

        $environment = (config('services.paypal.mode') === 'sandbox')
            ? new SandboxEnvironment($clientId, $clientSecret)
            : new ProductionEnvironment($clientId, $clientSecret);

        return new PayPalHttpClient($environment);
    }

    /**
     * Inicia el proceso de pago con PayPal para los productos del carrito.
     * Esta función crea la ORDEN DE PAYPAL y redirige al usuario.
     */
    public function payWithPaypal(Request $request)
    {
        Log::info('ProductPaymentController@payWithPaypal iniciado.');

        // 1. Obtener los productos del carrito usando tu CartController personalizado
        $cartController = new CartController(); // Instancia de tu CartController
        $revalidatedData = $cartController->revalidateAndCalculateCart(session()->get('cart', []));

        $cartItems = collect($revalidatedData['cart']); // Convierte el array a una colección
        $totalAmountLocalCurrency = $revalidatedData['total']; // Usa el total calculado por tu CartController

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Tu carrito está vacío. Añade productos para continuar.');
        }

        // 2. Calcular impuestos y envío en moneda local
        $shippingLocal = 0.00; // ADAPTA ESTO: Usa tu lógica real
        $taxLocal = 0.00;      // ADAPTA ESTO: Usa tu lógica real
        $totalFinalLocalCurrency = $totalAmountLocalCurrency + $shippingLocal + $taxLocal;

        // 3. Realizar conversión de moneda si es necesario
        $localCurrency = config('app.locale_currency', 'PEN');
        $paypalCurrency = config('services.paypal.currency', 'USD');

        $exchangeRate = null;
        if ($localCurrency !== $paypalCurrency) {
            $exchangeRate = $this->exchangeRateService->getExchangeRate($localCurrency, $paypalCurrency);
            if (is_null($exchangeRate)) {
                Log::error('No se pudo obtener la tasa de cambio entre ' . $localCurrency . ' y ' . $paypalCurrency . ' para productos.');
                return redirect()->back()->with('error', 'No se pudo obtener la tasa de cambio para procesar el pago. Inténtalo de nuevo.');
            }
        }

        $paypalItems = [];
        $calculatedItemTotal = 0;

        foreach ($cartItems->toArray() as $item) {
            $itemPriceInPayPalCurrency = ($localCurrency !== $paypalCurrency && $exchangeRate)
                ? round((float)$item['effective_price_per_unit'] * $exchangeRate, 2)
                : (float)$item['effective_price_per_unit'];

            $formattedItemPrice = sprintf("%.2f", $itemPriceInPayPalCurrency);

            $paypalItems[] = [
                'name' => $item['name'],
                'unit_amount' => [
                    'currency_code' => $paypalCurrency,
                    'value' => $formattedItemPrice,
                ],
                'quantity' => (string)$item['quantity'],
            ];

            $calculatedItemTotal += (float)$formattedItemPrice * (int)$item['quantity'];
        }

        $itemTotalForPayPal = sprintf("%.2f", $calculatedItemTotal);

        $shippingForPayPal = ($localCurrency !== $paypalCurrency && $exchangeRate)
            ? round($shippingLocal * $exchangeRate, 2)
            : $shippingLocal;
        $shippingForPayPal = sprintf("%.2f", $shippingForPayPal);

        $taxForPayPal = ($localCurrency !== $paypalCurrency && $exchangeRate)
            ? round($taxLocal * $exchangeRate, 2)
            : $taxLocal;
        $taxForPayPal = sprintf("%.2f", $taxForPayPal);

        $amountForPayPal = sprintf("%.2f", (float)$itemTotalForPayPal + (float)$shippingForPayPal + (float)$taxForPayPal);

        // 4. Crear la orden en tu base de datos (estado 'pending')
        try {
            $order = Order::create([
                'user_id' => Auth::id(), // DEBE ser Auth::id() si solo usuarios logueados pueden pagar
                'total_amount' => $totalFinalLocalCurrency,
                'currency' => $localCurrency,
                'status' => 'pending',
                'paypal_order_id' => null,
                'payment_details' => null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['effective_price_per_unit'],
                ]);
            }

            // 5. Preparar la solicitud a PayPal SDK
            $requestPayPal = new OrdersCreateRequest();
            $requestPayPal->prefer('return=representation');
            $requestPayPal->body = [
                "intent" => "CAPTURE",
                "purchase_units" => [[
                    "reference_id" => (string)$order->id,
                    "description" => "Compra de productos del carrito en " . config('app.name'),
                    "amount" => [
                        "currency_code" => $paypalCurrency,
                        "value" => $amountForPayPal,
                        "breakdown" => [
                            'item_total' => [
                                'currency_code' => $paypalCurrency,
                                'value' => $itemTotalForPayPal,
                            ],
                            'shipping' => [
                                'currency_code' => $paypalCurrency,
                                'value' => $shippingForPayPal,
                            ],
                            'tax_total' => [
                                'currency_code' => $paypalCurrency,
                                'value' => $taxForPayPal,
                            ],
                        ],
                    ],
                    "items" => $paypalItems,
                ]],
                "application_context" => [
                    "brand_name" => config('app.name', 'BlueyVet'),
                    "landing_page" => "BILLING",
                    "shipping_preference" => "NO_SHIPPING",
                    "user_action" => "PAY_NOW",
                    "return_url" => route('cart_payments.success'),
                    "cancel_url" => route('cart_payments.cancel', ['order_id_local' => $order->id])
                ]
            ];

            // 6. Ejecutar la solicitud a PayPal
            $client = $this->client();
            Log::info('PayPal Request Body (Product Create Order):', ['body' => json_encode($requestPayPal->body, JSON_PRETTY_PRINT)]);

            $response = $client->execute($requestPayPal);

            Log::info('PayPal Create Product Order Response:', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);

            if (isset($response->result->id)) {
                $order->paypal_order_id = $response->result->id;
                $order->save();

                foreach ($response->result->links as $link) {
                    if ($link->rel == 'approve') {
                        return redirect()->away($link->href);
                    }
                }
            } else {
                Log::error('PayPal no devolvió un ID de Orden para productos en createOrder:', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);
                return redirect()->back()->with('error', 'No se pudo obtener el ID de la orden de PayPal para tu compra de productos.');
            }
        } catch (HttpException $ex) {
            $errorDetails = json_decode($ex->getMessage(), true);
            Log::error('Error de PayPal (HttpException) al crear la orden de productos:', [
                'status' => $ex->statusCode,
                'message' => $ex->getMessage(),
                'details' => $errorDetails,
                'trace' => $ex->getTraceAsString()
            ]);
            if (isset($order) && $order->exists()) {
                $order->update(['status' => 'failed', 'payment_details' => json_encode($errorDetails)]);
            }
            return redirect()->back()->with('error', 'Error al crear la orden de PayPal para productos: ' . ($errorDetails['message'] ?? 'Error desconocido.'));
        } catch (\Exception $ex) {
            Log::error('Error general al crear la orden de PayPal para productos:', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString()
            ]);
            if (isset($order) && $order->exists()) {
                $order->update(['status' => 'failed', 'payment_details' => json_encode(['error' => $ex->getMessage()])]);
            }
            return redirect()->back()->with('error', 'Ocurrió un error inesperado al procesar tu carrito. Por favor, inténtalo de nuevo más tarde.');
        }
    }

    /**
     * Maneja el callback de PayPal después de un pago exitoso para productos.
     */
    public function paypalSuccess(Request $request)
    {
        Log::info('Accediendo a cart_payments.success', $request->all());
        $token = $request->get('token');

        if (empty($token)) {
            Log::error('Token de orden de PayPal faltante en la URL de éxito para productos.');
            return redirect()->route('cart.index')->with('error', 'Faltan datos para verificar tu pago. Por favor, contacta a soporte.');
        }

        $client = $this->client();
        $order = null;

        try {
            // 1. Obtener los detalles de la orden de PayPal
            $requestGetOrder = new OrdersGetRequest($token);
            $response = $client->execute($requestGetOrder);

            Log::info('PayPal Get Product Order Response:', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);

            $paypalOrderId = $response->result->id;
            $purchaseUnit = $response->result->purchase_units[0];
            $referenceId = $purchaseUnit->reference_id ?? null;

            if (empty($referenceId)) {
                Log::error('reference_id no encontrado en la respuesta de PayPal (productos), no se puede vincular a la orden local.');
                return redirect()->route('cart.index')->with('error', 'Pago exitoso, pero no pudimos vincularlo a tu orden de productos. Contacta a soporte.');
            }

            $order = Order::find($referenceId);

            if (!$order) {
                Log::error('Order local no encontrada en cart_payments.success.', ['reference_id' => $referenceId, 'paypal_order_id' => $paypalOrderId]);
                return redirect()->route('cart.index')->with('error', 'La orden de productos asociada a tu pago no pudo ser encontrada.');
            }

            // Verificar si la orden ya fue completada para evitar doble procesamiento
            if (strtolower($order->status) === 'completed') {
                Log::info('La orden de productos local ' . $order->id . ' ya estaba completada. Redirigiendo a página de confirmación.');
                Session::flash('info', 'Tu pago de productos ya había sido confirmado previamente.');
                // Usar la nueva ruta 'cart_payments.order_details'
                return redirect()->route('cart_payments.order_details', $order->id);
            }

            // 2. Capturar el pago si la orden de PayPal está en estado 'APPROVED'
            if ($response->result->status == 'APPROVED') {
                $requestCapture = new OrdersCaptureRequest($token);
                $responseCapture = $client->execute($requestCapture);

                Log::info('PayPal Capture Product Order Response (after APPROVED):', ['response' => json_encode($responseCapture->result, JSON_PRETTY_PRINT)]);

                if (isset($responseCapture->result->status) && $responseCapture->result->status == 'COMPLETED') {
                    $order->status = 'completed';
                    $order->paypal_order_id = $paypalOrderId;
                    $order->paypal_payment_id = $responseCapture->result->purchase_units[0]->payments->captures[0]->id ?? null;
                    $order->payment_details = json_encode($responseCapture->result);
                    $order->save();

                    Session::forget('cart');

                    foreach ($order->items as $orderItem) {
                        $product = Product::find($orderItem->product_id);
                        if ($product && $product->stock >= $orderItem->quantity) {
                            $product->decrement('stock', $orderItem->quantity);
                            Log::info('Stock decrementado para producto ID: ' . $product->id . ', cantidad: ' . $orderItem->quantity);
                        } else {
                            Log::warning('No se pudo decrementar el stock para producto ID: ' . ($product->id ?? $orderItem->product_id) . '. Stock actual: ' . ($product->stock ?? 'N/A') . ', cantidad pedida: ' . $orderItem->quantity);
                        }
                    }

                    try {
                        $recipientEmail = $order->user ? $order->user->email : ($responseCapture->result->payer->email_address ?? null);
                        if ($recipientEmail) {
                            Mail::to($recipientEmail)->send(new ProductOrderConfirmationMail($order));
                            Log::info('Correo de confirmación de pedido de productos ' . $order->id . ' enviado a: ' . $recipientEmail);
                        } else {
                            Log::warning("No recipient email found for product order ID: {$order->id}. Cannot send confirmation email.");
                        }
                    } catch (\Exception $mailEx) {
                        Log::error("Error al enviar correo de confirmación para la orden de productos " . $order->id . ": " . $mailEx->getMessage());
                    }

                    Session::flash('success', '¡Pago de productos procesado con éxito! Tu pedido ha sido confirmado.');
                    // Usar la nueva ruta 'cart_payments.order_details'
                    return redirect()->route('cart_payments.order_details', $order->id);
                } else {
                    $order->status = 'failed';
                    $order->payment_details = json_encode($responseCapture->result);
                    $order->save();
                    Log::error('PayPal Capture Product Order Failed:', ['order_id' => $order->id, 'response' => $responseCapture->result]);
                    return redirect()->route('cart.index')->with('error', $responseCapture->result->message ?? 'El pago de productos no se pudo completar. Por favor, inténtalo de nuevo.');
                }
            } else {
                $order->status = 'failed';
                $order->payment_details = json_encode($response->result);
                $order->save();
                Log::warning('Orden de PayPal de productos no aprobada o estado inesperado: ' . ($response->result->status ?? 'N/A') . ', ID de Orden: ' . $paypalOrderId);
                return redirect()->route('cart.index')->with('error', 'El estado de la orden de PayPal no es válido para la captura. Estado: ' . ($response->result->status ?? 'Desconocido'));
            }
        } catch (HttpException $ex) {
            $errorDetails = json_decode($ex->getMessage(), true);
            Log::error('Error de PayPal (HttpException) al obtener/capturar la orden de productos en success:', [
                'status' => $ex->statusCode,
                'message' => $ex->getMessage(),
                'details' => $errorDetails,
                'trace' => $ex->getTraceAsString()
            ]);
            if (isset($order) && $order->exists()) {
                $order->update(['status' => 'failed', 'payment_details' => json_encode($errorDetails)]);
            }
            return redirect()->route('cart.index')->with('error', 'Error al procesar el pago de productos con PayPal: ' . ($errorDetails['message'] ?? 'Error desconocido.'));
        } catch (\Exception $ex) {
            Log::error('Error general al procesar la orden de PayPal de productos en success:', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString()
            ]);
            if (isset($order) && $order->exists()) {
                $order->update(['status' => 'failed', 'payment_details' => json_encode(['error' => $ex->getMessage()])]);
            }
            return redirect()->route('cart.index')->with('error', 'Ocurrió un error interno al verificar tu pago de productos. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Maneja la redirección de PayPal cuando el usuario cancela el pago de productos.
     */
    public function paypalCancel(Request $request)
    {
        Log::info('Método de cancelación de PayPal de productos iniciado.', ['request_query' => $request->query()]);

        $localOrderId = $request->query('order_id_local');
        $token = $request->query('token');

        if ($localOrderId) {
            $order = Order::find($localOrderId);
            if ($order) {
                if (strtolower($order->status) === 'pending') {
                    $order->update(['status' => 'cancelled']);
                    Log::info('Orden de productos local ' . $localOrderId . ' marcada como CANCELADA.');
                    Session::flash('info', 'El pago de tu carrito fue cancelado. Puedes intentar de nuevo.');
                } else {
                    Log::warning('La orden de productos local ' . $localOrderId . ' no pudo ser marcada como cancelada porque no estaba en estado "pending". Estado actual: ' . $order->status);
                    Session::flash('warning', 'Tu pedido ya no estaba en estado pendiente. Si crees que hay un error, contacta a soporte.');
                }
            } else {
                Log::warning('Orden de productos local ' . $localOrderId . ' no encontrada al intentar cancelar.');
                Session::flash('error', 'La orden de productos a cancelar no pudo ser encontrada.');
            }
        } else {
            Log::warning('No se recibió order_id_local en la URL de cancelación de productos.');
            Session::flash('error', 'No se pudo identificar tu pedido para cancelar. Intenta de nuevo.');
        }

        return redirect()->route('cart.index')->with('warning', 'Has cancelado el proceso de pago de tus productos. Puedes volver a intentarlo cuando quieras.');
    }

    /**
     * Muestra la vista de confirmación para pedidos de productos.
     * Implementa la verificación de autenticación y autorización.
     */
    public function showProductOrderConfirmedView(Order $order)
    {
        Log::info('Accediendo a ProductPaymentController@showProductOrderConfirmedView para la orden: ' . $order->id);

        // 1. Verificar si el usuario está autenticado
        if (!Auth::check()) {
            Log::warning('Intento de acceso a orden confirmada sin sesión iniciada.', ['order_id' => $order->id]);
            // Redirige al login con un mensaje. Laravel manejará la URL de retorno después del login.
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver los detalles de tu pedido.');
        }

        // 2. Verificar que la orden pertenece al usuario autenticado
        // Asumiendo que tu modelo Order tiene un 'user_id' que relaciona con el usuario.
        if (Auth::id() !== $order->user_id) {
            Log::warning('Intento de acceso no autorizado a orden.', [
                'order_id' => $order->id,
                'logged_in_user_id' => Auth::id(),
                'order_owner_user_id' => $order->user_id
            ]);
            // Aborta con 403 (Forbidden) o redirige a una página de error genérica.
            abort(403, 'No tienes permiso para ver esta orden.');
            // O una redirección más amigable:
            // return redirect()->route('client.home')->with('error', 'No tienes permiso para ver esta orden.');
        }

        // 3. Verificar el estado de la orden (opcional, pero buena práctica)
        if ($order->status !== 'completed') {
            Log::warning('Intento de ver orden de productos con estado no completado.', ['order_id' => $order->id, 'current_status' => $order->status]);
            return redirect()->route('cart.index')->with('error', 'El estado de este pedido no permite su visualización.');
        }

        // Si todas las verificaciones pasan, mostrar la vista
        return view('client.checkout.product-order-confirmation', compact('order'));
    }
}