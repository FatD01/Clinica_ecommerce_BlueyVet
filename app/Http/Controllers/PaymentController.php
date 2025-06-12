<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\Veterinarian;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ServiceContactMail;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\ExchangeRateService; // <--- Asegúrate de que este use esté presente y correcto

// ¡ASEGÚRATE DE TENER ESTAS LÍNEAS EXACTAMENTE ASÍ!
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException; // <--- ¡¡¡AÑADE ESTA LÍNEA AQUÍ!!!


class PaymentController extends Controller
{


     protected $exchangeRateService; // Declara la propiedad

    public function __construct(ExchangeRateService $exchangeRateService) // Inyecta en el constructor
    {
        $this->exchangeRateService = $exchangeRateService;
    }
    /**
     * Configura el cliente de PayPal.
     * @return PayPalHttpClient
     */
    private function client()
    {
        $clientId = config('services.paypal.sandbox.client_id');
        $clientSecret = config('services.paypal.sandbox.client_secret');

        $environment = (config('services.paypal.mode') === 'sandbox')
            ? new SandboxEnvironment($clientId, $clientSecret)
            : new ProductionEnvironment($clientId, $clientSecret);

        return new PayPalHttpClient($environment);
    }

    /**
     * Muestra la página de checkout al usuario.
     */
    public function showCheckoutPage(Service $service)
    {
        $order = ServiceOrder::where('user_id', Auth::id())
            ->where('service_id', $service->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            $order = ServiceOrder::create([
                'user_id' => Auth::id(),
                'service_id' => $service->id,
                'amount' => $service->price,
                'currency' => config('services.paypal.local_currency', 'PEN'),
                'status' => 'pending',
            ]);
        }

        return view('client.checkout', [
            'service' => $service,
            'order' => $order,
        ]);
    }

    /**
     * Este método es llamado por el JavaScript del SDK de PayPal (función `createOrder`)
     * para crear la orden de PayPal en el backend.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'amount' => 'required|numeric|min:0.01',
            'service_name' => 'required|string',
            'order_id' => 'required|exists:service_orders,id',
        ]);

        $service = Service::find($request->service_id);
        $order = ServiceOrder::find($request->order_id);

        if (!$order || $order->user_id !== Auth::id() || $order->status !== 'pending' || $service->price != $request->amount) {
            Log::warning('Acceso no autorizado o datos inválidos en payments.checkout. Orden: ' . ($order->id ?? 'N/A') . ' User: ' . Auth::id());
            return response()->json(['error' => 'Acceso no autorizado o datos inválidos para la orden.'], 403);
        }

        // --- INICIO DE LA LÓGICA DE CONVERSIÓN DE MONEDA (PEN a USD) ---
        $localCurrency = config('services.paypal.local_currency', 'PEN'); // Esto debería ser 'PEN'
        $paypalCurrency = config('services.paypal.currency', 'USD'); // Esto debería ser 'USD'

        $amountInLocalCurrency = $service->price; // Monto original en PEN (ej. 46.00)
        $amountForPayPal = $amountInLocalCurrency; // Se inicializa con el monto local

        // SOLO si la moneda local es diferente a la de PayPal (PEN vs USD)
        if ($localCurrency !== $paypalCurrency) {
            $exchangeRate = $this->exchangeRateService->getExchangeRate($localCurrency, $paypalCurrency);

            if (is_null($exchangeRate)) {
                Log::error('No se pudo obtener la tasa de cambio entre ' . $localCurrency . ' y ' . $paypalCurrency . '. Asegúrate de que OPENEXCHANGERATES_API_KEY esté configurada correctamente y sea válida.');
                return response()->json(['error' => 'No se pudo obtener la tasa de cambio para procesar el pago.'], 500);
            }
            // ¡Esta es la línea clave que hace la conversión de PEN a USD!
            $amountForPayPal = round($amountInLocalCurrency * $exchangeRate, 2); // Convertir PEN a USD y redondear a 2 decimales
        }

        Log::info('Detalles de la conversión de moneda para PayPal:', [
            'local_amount' => $amountInLocalCurrency,
            'local_currency' => $localCurrency,
            'paypal_currency' => $paypalCurrency,
            'exchange_rate' => $exchangeRate ?? 'N/A', // Mostrar N/A si no hubo conversión (ej. si local y paypal currency son iguales)
            'amount_sent_to_paypal' => $amountForPayPal // ¡Este es el monto que se enviará a PayPal!
        ]);
        // --- FIN DE LA LÓGICA DE CONVERSIÓN ---

        $requestPayPal = new OrdersCreateRequest();
        $requestPayPal->prefer('return=representation');
        $requestPayPal->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => $order->id,
                "description" => $service->name,
                "amount" => [
                    "currency_code" => $paypalCurrency, // <--- ¡AQUÍ USA LA MONEDA OBJETIVO!
                    "value" => number_format($amountForPayPal, 2, '.', ''), // <--- ¡¡¡AQUÍ USA LA VARIABLE CONVERTIDA!!!
                ]
            ]],
            "application_context" => [
                "brand_name" => config('app.name', 'BlueyVet'),
                "landing_page" => "BILLING",
                "shipping_preference" => "NO_SHIPPING",
                "user_action" => "PAY_NOW",
                "return_url" => route('payments.success'),
                "cancel_url" => route('payments.cancel', ['order_id_local' => $order->id])
            ]
        ];

        try {
            $client = $this->client();
            $response = $client->execute($requestPayPal);

            // Log la respuesta completa de PayPal
            Log::info('Respuesta de PayPal (OrdersCreateRequest):', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);

            // Asegurarse de que el ID exista antes de intentar acceder a él
            if (isset($response->result->id)) {
                return response()->json(['id' => $response->result->id]);
            } else {
                Log::error('PayPal no devolvió un Order ID en createOrder response:', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);
                return response()->json(['error' => 'No se pudo obtener el ID de la orden de PayPal.'], 500);
            }
        } catch (HttpException $ex) {
            Log::error('Error de PayPal (HttpException) al crear orden:', [
                'status' => $ex->statusCode,
                'message' => $ex->getMessage(),
                'details' => json_decode($ex->getMessage(), true),
                'trace' => $ex->getTraceAsString()
            ]);
            // Asegurarse de que el error es JSON si se intenta devolver
            return response()->json(['error' => 'Error al crear la orden de PayPal: ' . $ex->getMessage(), 'details' => json_decode($ex->getMessage(), true)], $ex->statusCode ?: 500);
        } catch (\Exception $ex) {
            Log::error('Error general al crear orden de PayPal:', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error interno del servidor al crear la orden.'], 500);
        }
    }
    public function success(Request $request)
    {
        Log::info('Acceso a payments.success', $request->all()); // Log para ver qué llega
        $token = $request->get('token'); // Este es el Order ID de PayPal
        $payerId = $request->get('PayerID');

        if (empty($token) || empty($payerId)) {
            Log::error('Faltan parámetros en la URL de éxito de PayPal.');
            Session::flash('error', 'Faltan datos para procesar el pago. Por favor, intenta de nuevo.');
            return redirect()->route('checkout.failed'); // Redirige a una página de fallo
        }

        $client = $this->client(); // Obtener el cliente de PayPal

        try {
            $requestGetOrder = new OrdersGetRequest($token); // Usamos OrdersGetRequest
            $response = $client->execute($requestGetOrder);

            // Log la respuesta completa de la captura
            Log::info('Respuesta de PayPal (OrdersGetRequest):', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);

            if ($response->statusCode == 200 && $response->result->status == 'COMPLETED') {
                $paypalOrderId = $response->result->id; // El mismo token
                $purchaseUnit = $response->result->purchase_units[0];
                $referenceId = $purchaseUnit->reference_id; // Este debería ser tu order_id_local (el ID de ServiceOrder)

                // Buscar tu orden local por el reference_id (el ID de tu ServiceOrder)
                $order = ServiceOrder::find($referenceId);

                // Verificamos si la orden existe, pertenece al usuario autenticado, y está pendiente
                if ($order && $order->user_id === Auth::id() && $order->status === 'pending') {
                    $order->status = 'completed';
                    $order->paypal_order_id = $paypalOrderId; // Guardar el ID de PayPal
                    $order->payer_id = $payerId; // Guardar el PayerID
                    $order->save();

                    // ******************************************************
                    // LÓGICA PARA ENVIAR EL CORREO ELECTRÓNICO DE CONFIRMACIÓN
                    // ******************************************************
                    // Asegúrate de que el usuario tiene un correo electrónico asociado a la orden
                    // $order->user es la relación (ServiceOrder belongsTo User)
                    if ($order->user && $order->user->email) {
                        try {
                            Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
                            Log::info('Correo de confirmación de orden ' . $order->id . ' enviado a: ' . $order->user->email);
                        } catch (\Exception $mailEx) {
                            Log::error('Error al enviar correo de confirmación para orden ' . $order->id . ': ' . $mailEx->getMessage(), ['trace' => $mailEx->getTraceAsString()]);
                            // No queremos que el fallo del correo impida que el usuario vea la página de éxito
                            Session::flash('warning', 'Tu pago fue procesado correctamente, pero no pudimos enviar el correo de confirmación. Por favor, revisa tu bandeja de spam o contacta a soporte.');
                        }
                    } else {
                        Log::warning('No se pudo enviar correo de confirmación: usuario o email no encontrado para orden ' . $order->id);
                        Session::flash('warning', 'Tu pago fue procesado correctamente, pero no pudimos enviar el correo de confirmación porque no se encontró el email del usuario.');
                    }
                    // ******************************************************
                    // FIN LÓGICA PARA ENVIAR EL CORREO ELECTRÓNICO
                    // ******************************************************

                    Session::flash('success', '¡Pago realizado con éxito! Tu orden ha sido confirmada.');
                    return redirect()->route('checkout.success_page'); // Una página de éxito real que debes crear
                } else if ($order && $order->status === 'completed') {
                    // La orden ya estaba completada (posible reintento o doble notificación)
                    Log::info('Orden local ' . $referenceId . ' ya estaba completada. No se realizó ninguna acción.');
                    Session::flash('info', 'Tu pago ya había sido confirmado previamente.');
                    return redirect()->route('checkout.success_page');
                }
                else {
                    Log::warning('Orden local no encontrada, usuario no coincide, o estado incorrecto en payments.success. Reference ID: ' . $referenceId . ', User: ' . Auth::id());
                    Session::flash('warning', 'El pago fue procesado en PayPal, pero hubo un problema al actualizar el estado de tu orden local. Por favor, contacta a soporte.');
                    return redirect()->route('checkout.error_page'); // Redirige a una página de error
                }
            } else {
                // La orden de PayPal no está en estado COMPLETED (puede ser PENDING, VOIDED, etc.)
                Log::warning('Orden de PayPal no completada. Estado: ' . ($response->result->status ?? 'N/A') . ', PayPal Order ID: ' . $token);
                Session::flash('error', 'El pago no se pudo completar. Por favor, intenta de nuevo o contacta a soporte.');
                return redirect()->route('checkout.failed');
            }
        } catch (HttpException $ex) {
            Log::error('Error de PayPal (HttpException) al obtener orden:', [
                'status' => $ex->statusCode,
                'message' => $ex->getMessage(),
                'details' => json_decode($ex->getMessage(), true)
            ]);
            Session::flash('error', 'Error al procesar el pago con PayPal: ' . $ex->getMessage());
            return redirect()->route('checkout.failed');
        } catch (\Exception $ex) {
            Log::error('Error general al obtener orden de PayPal:', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString()
            ]);
            Session::flash('error', 'Ocurrió un error interno al procesar tu pago. Por favor, intenta de nuevo.');
            return redirect()->route('checkout.failed');
        }
    }

    /**
     * Maneja la cancelación del pago de PayPal. (Este método se mantiene igual)
     */
    public function cancel(Request $request)
    {
        Log::info('Inicio del método cancel de PayPal.', ['request_query' => $request->query()]);

        $localOrderId = $request->query('order_id_local');

        if ($localOrderId) {
            $order = ServiceOrder::find($localOrderId);
            if ($order) {
                if ($order->status === 'pending') {
                    $order->update(['status' => 'cancelled']);
                    Log::info('Orden local ' . $localOrderId . ' marcada como cancelada.');
                    Session::flash('info', 'El pago de tu orden fue cancelado. Puedes intentar de nuevo.');
                } else {
                    Log::warning('Orden local ' . $localOrderId . ' no se pudo marcar como cancelada porque ya no estaba en estado "pending". Estado actual: ' . $order->status);
                    Session::flash('warning', 'La orden ya no estaba en estado pendiente. Si crees que hay un error, contacta a soporte.');
                }
            } else {
                Log::warning('Orden local ' . $localOrderId . ' no encontrada al intentar cancelar.');
                Session::flash('error', 'La orden a cancelar no pudo ser encontrada.');
            }
        } else {
            Log::warning('No se recibió order_id_local en la URL de cancelación.');
            Session::flash('error', 'No se pudo identificar la orden para cancelar. Intenta de nuevo.');
        }

        // Si la solicitud es AJAX (por ejemplo, desde los botones inteligentes de PayPal),
        // podría esperar una respuesta JSON. Aunque PayPal en la cancelación suele hacer una redirección GET.
        if ($request->expectsJson()) {
            return response()->json(['status' => 'CANCELLED', 'message' => 'Pago cancelado.']);
        }

        // Redirige a la vista de cancelación que ya tienes
        return redirect()->route('checkout.failed'); // Usamos la misma página de fallo general para el usuario.
        // O puedes tener una ruta específica como 'checkout.cancelled_page'
        // si quieres un mensaje diferente.
    }
    /**
     * Muestra el formulario de cita después de un pago exitoso. (Este método se mantiene igual)
     */
    public function showAppointmentForm(ServiceOrder $order)
    {
        if ($order->status !== 'completed' || $order->user_id !== Auth::id()) {
            return redirect()->route('client.servicios.index')->with('error', 'Acceso no autorizado al formulario de cita o pago no completado para esta orden.');
        }
        $veterinarians = Veterinarian::all();
        return view('client.appointment_form', [
            'order' => $order,
            'service' => $order->service,
            'veterinarians' => $veterinarians,
        ]);
    }

    /**
     * Almacena los datos del formulario de cita (después del pago). (Este método se mantiene igual)
     */
    public function storeAppointment(Request $request, ServiceOrder $order)
    {
        if ($order->status !== 'completed' || $order->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'No se puede procesar la cita. La orden no está pagada o no te pertenece.');
        }

        $validatedData = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'veterinario' => 'required|string|max:255',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'mensaje' => 'nullable|string|max:1000',
        ]);

        Mail::to('fatima.rodriguez@tecsup.edu.pe')->send(new ServiceContactMail($validatedData));

        return redirect()->route('client.home')->with('success', '¡Tu cita ha sido agendada y tu solicitud enviada! Nos pondremos en contacto pronto para confirmarla.');
    }
}
