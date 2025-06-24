<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\ExchangeRateService;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException;


class PaymentController extends Controller
{
    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

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
     * Muestra la página de checkout al usuario.
     * Este método recibe el ID de una ServiceOrder *ya creada* (en estado PENDING).
     *
     * @param int $serviceOrderId El ID de la ServiceOrder a pagar.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    //aqui solo pondre como string en vez de int, así estaaba antes: int $serviceOrderId || ya reverti esto
    public function showCheckoutPage(int $serviceOrderId)
    {
        Log::info('Accediendo a PaymentController@showCheckoutPage', ['service_order_id' => $serviceOrderId]);

        $order = ServiceOrder::with('service')->find($serviceOrderId);

        if (!$order) {
            Log::error('ServiceOrder no encontrada para el ID proporcionado en showCheckoutPage.', ['service_order_id' => $serviceOrderId]);
            Session::flash('error', 'La orden de pago no pudo ser encontrada. Por favor, intenta de nuevo.');
            return redirect()->route('client.home');
        }

        if ($order->user_id !== Auth::id()) {
            Log::error('Intento de acceso no autorizado a ServiceOrder en showCheckoutPage.', ['service_order_id' => $serviceOrderId, 'current_user_id' => Auth::id(), 'order_user_id' => $order->user_id]);
            Session::flash('error', 'No tienes permiso para acceder a esta orden de pago.');
            return redirect()->route('client.home');
        }

        if (strtolower($order->status) === 'completed') {
            Log::warning('Intento de pagar una ServiceOrder ya completada.', ['service_order_id' => $serviceOrderId, 'order_status' => $order->status]);
            Session::flash('info', 'Esta orden ya ha sido pagada. Redirigiendo.');
            // Si ya está pagada, redirige a la creación de citas con el ID de la orden para vincular
            $relatedAppointment = Appointment::where('service_order_id', $order->id)->first();
            if (!$relatedAppointment) {
                return redirect()->route('client.citas.create', ['preselected_service_order_id' => $order->id]);
            } else {
                return redirect()->route('client.citas.index')->with('info', 'Esta orden ya está vinculada a una cita existente.');
            }
        }

        if (strtolower($order->status) !== 'pending') {
            Log::warning('Intento de pagar una ServiceOrder que no está en estado pendiente (estado actual: ' . $order->status . ').', ['service_order_id' => $serviceOrderId, 'order_status' => $order->status]);
            Session::flash('error', 'Esta orden ya no está pendiente de pago. Por favor, intenta de nuevo.');
            return redirect()->route('client.home');
        }

        $service = $order->service;

        // Almacena el ID de la ServiceOrder en la sesión para el callback de PayPal (si no está ya ahí)
        Session::put('current_service_order_id_for_payment', $order->id);

        return view('client.checkout', [
            'service' => $service,
            'order' => $order,
        ]);
    }
    /**
     * Muestra la vista de confirmación de transacción completada (client.checkout.success).
     *
     * @param int $serviceOrderId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showCompletedTransactionView(int $serviceOrderId)
    {
        $order = ServiceOrder::with('service', 'user')->find($serviceOrderId);

        if (!$order || $order->user_id !== Auth::id()) {
            Log::warning('Acceso no autorizado o ServiceOrder no encontrada para showCompletedTransactionView.', ['service_order_id' => $serviceOrderId, 'user_id' => Auth::id()]);
            return redirect()->route('client.home')->with('error', 'No se pudo cargar la confirmación de pago.');
        }

        // Aunque success() ya verifica el estado, es una buena práctica aquí también.
        if (strtolower($order->status) !== 'completed') {
            Log::warning('Intento de acceder a la página de confirmación para una orden no completada.', ['service_order_id' => $serviceOrderId, 'status' => $order->status]);
            return redirect()->route('client.home')->with('warning', 'Esta orden aún no ha sido pagada o está en proceso.');
        }

        // Recupera y muestra el mensaje flash si existe
        if (Session::has('payment_success_message')) {
            Session::now('success', Session::get('payment_success_message'));
            // No uses forget aquí, ya que Session::now lo limpia después de mostrarse en la vista actual
        }

        return view('client.checkout.success', [
            'order' => $order,
            'paypal_order_id' => $order->paypal_order_id,
            'payment_details' => json_decode($order->payment_details, true),
        ]);
    }

    /**
     * Maneja la compra directa de un servicio desde la página /servicios.
     * Crea una nueva ServiceOrder y redirige a la página de checkout.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function purchaseService(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $service = Service::findOrFail($request->service_id);

        try {
            $newServiceOrder = ServiceOrder::create([
                'user_id' => Auth::id(),
                'service_id' => $service->id,
                'status' => 'PENDING',
                'amount' => $service->price,
                'currency' => config('app.locale_currency', 'PEN'), // Usa APP_LOCALE_CURRENCY de .env
            ]);

            // Almacena el ID de la ServiceOrder en la sesión para el callback de PayPal
            Session::put('current_service_order_id_for_payment', $newServiceOrder->id);

            Log::info('Nueva ServiceOrder PENDIENTE creada para la compra directa de servicios.', [
                'service_order_id' => $newServiceOrder->id,
                'user_id' => Auth::id(),
                'service_id' => $service->id,
            ]);

            return redirect()->route('payments.show_checkout_page', ['serviceOrderId' => $newServiceOrder->id])
                ->with('info', 'Por favor, completa el pago para adquirir el servicio.');
        } catch (\Exception $e) {
            Log::error('Error al iniciar la compra de servicios en PaymentController@purchaseService: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Hubo un error al preparar la compra del servicio. Inténtalo de nuevo.');
        }
    }


    /**
     * Este método es llamado por el JavaScript SDK de PayPal (`createOrder` function)
     * para crear la orden de PayPal en el backend.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        // NOTA: La solicitud ya debería contener order_id_local con el ID de la ServiceOrder
        // El service_id, amount, service_name se pasan desde el frontend para construir la orden de PayPal.
        $request->validate([
            'service_id' => 'required|exists:services,id', // Usado para la descripción de PayPal
            'amount' => 'required|numeric|min:0.01',       // Usado para el monto de PayPal
            'service_name' => 'required|string',           // Usado para la descripción de PayPal
            'order_id' => 'required|exists:service_orders,id', // ESTE ES TU ID DE SERVICEORDER LOCAL
        ]);

        $service = Service::find($request->service_id);
        $order = ServiceOrder::find($request->order_id);

        if (!$order || $order->user_id !== Auth::id() || strtolower($order->status) !== 'pending' || $service->price != $request->amount) {
            Log::warning('Acceso no autorizado o datos inválidos en payments.checkout. Orden: ' . ($order->id ?? 'N/A') . ' Usuario: ' . Auth::id() . ' Estado: ' . ($order->status ?? 'N/A') . ' Precio Servicio: ' . ($service->price ?? 'N/A') . ' Monto Solicitado: ' . $request->amount);
            return response()->json(['error' => 'Acceso no autorizado o datos inválidos para la orden.'], 403);
        }

        $amountInLocalCurrency = $order->amount; // Usa el monto de la ServiceOrder
        $localCurrency = config('app.locale_currency', 'PEN'); // Usa APP_LOCALE_CURRENCY de .env
        $paypalCurrency = config('services.paypal.currency', 'USD');

        $amountForPayPal = $amountInLocalCurrency;
        $exchangeRate = null;

        if ($localCurrency !== $paypalCurrency) {
            $exchangeRate = $this->exchangeRateService->getExchangeRate($localCurrency, $paypalCurrency);
            if (is_null($exchangeRate)) {
                Log::error('No se pudo obtener la tasa de cambio entre ' . $localCurrency . ' y ' . $paypalCurrency . '.');
                return response()->json(['error' => 'No se pudo obtener la tasa de cambio para procesar el pago.'], 500);
            }
            $amountForPayPal = round($amountInLocalCurrency * $exchangeRate, 2);
        }

        Log::info('Detalles de conversión de moneda para PayPal:', [
            'local_amount' => $amountInLocalCurrency,
            'local_currency' => $localCurrency,
            'paypal_currency' => $paypalCurrency,
            'exchange_rate' => $exchangeRate ?? 'N/A',
            'amount_sent_to_paypal' => $amountForPayPal
        ]);

        $requestPayPal = new OrdersCreateRequest();
        $requestPayPal->prefer('return=representation');
        $requestPayPal->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                // El reference_id DEBE ser el ID de tu ServiceOrder local
                "reference_id" => (string)$order->id, // Convertir a string es una buena práctica para PayPal
                "description" => $service->name,
                "amount" => [
                    "currency_code" => $paypalCurrency,
                    "value" => number_format($amountForPayPal, 2, '.', ''),
                ]
            ]],
            "application_context" => [
                "brand_name" => config('app.name', 'BlueyVet'),
                "landing_page" => "BILLING",
                "shipping_preference" => "NO_SHIPPING",
                "user_action" => "PAY_NOW",
                "return_url" => route('payments.success'),
                "cancel_url" => route('payments.cancel', ['order_id_local' => $order->id]) // Pasa el ID local para la cancelación
            ]
        ];

        try {
            $client = $this->client();
            $response = $client->execute($requestPayPal);

            Log::info('Respuesta de PayPal (OrdersCreateRequest):', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);

            if (isset($response->result->id)) {
                return response()->json(['id' => $response->result->id]);
            } else {
                Log::error('PayPal no devolvió un ID de Orden en la respuesta de createOrder:', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);
                return response()->json(['error' => 'No se pudo obtener el ID de la orden de PayPal.'], 500);
            }
        } catch (HttpException $ex) {
            Log::error('Error de PayPal (HttpException) al crear la orden:', [
                'status' => $ex->statusCode,
                'message' => $ex->getMessage(),
                'details' => json_decode($ex->getMessage(), true),
                'trace' => $ex->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al crear la orden de PayPal: ' . $ex->getMessage(), 'details' => json_decode($ex->getMessage(), true)], $ex->statusCode ?: 500);
        } catch (\Exception $ex) {
            Log::error('Error general al crear la orden de PayPal:', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error interno del servidor al crear la orden.'], 500);
        }
    }

    /**
     * Maneja el callback de PayPal después de un pago exitoso.
     * Verifica el estado de la orden de PayPal y actualiza la ServiceOrder local.
     * Luego, redirige a CitaController o a una página de confirmación.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
{
    Log::info('Accediendo a payments.success', $request->all());
    $token = $request->get('token');
    $payerId = $request->get('PayerID');

    if (empty($token) || empty($payerId)) {
        Log::error('Parámetros faltantes en la URL de éxito de PayPal.');
        Session::flash('error', 'Faltan datos para procesar el pago. Por favor, intenta de nuevo.');
        return redirect()->route('client.home')->with('error', 'Faltan datos para procesar el pago. Por favor, intenta de nuevo.');
    }

    $client = $this->client();

    try {
        $requestGetOrder = new OrdersGetRequest($token);
        $response = $client->execute($requestGetOrder);

        Log::info('Respuesta de PayPal (OrdersGetRequest):', ['response' => json_encode($response->result, JSON_PRETTY_PRINT)]);

        if ($response->statusCode == 200 && $response->result->status == 'COMPLETED') {
            $paypalOrderId = $response->result->id;
            $purchaseUnit = $response->result->purchase_units[0];
            $referenceId = $purchaseUnit->reference_id ?? null; // Esto es el service_order_id

            if (empty($referenceId)) {
                Log::error('reference_id no encontrado en la respuesta de PayPal, no se puede vincular a la orden local.');
                Session::flash('error', 'Pago exitoso, pero no pudimos vincularlo a tu orden. Contacta a soporte.');
                return redirect()->route('client.home');
            }

            $order = ServiceOrder::find($referenceId);

            if (!$order) {
                Log::error('ServiceOrder local no encontrada en payments.success.', ['reference_id' => $referenceId, 'paypal_order_id' => $paypalOrderId]);
                Session::flash('error', 'La orden de servicio asociada a tu pago no pudo ser encontrada.');
                return redirect()->route('client.home');
            }

            if (Auth::check() && $order->user_id !== Auth::id()) {
                Log::error('Intento de actualización no autorizado de ServiceOrder en payments.success.', ['order_id' => $order->id, 'order_user_id' => $order->user_id, 'auth_user_id' => Auth::id()]);
                Session::flash('error', 'Acceso no autorizado para esta orden de pago.');
                return redirect()->route('client.home');
            }

            $is_new_appointment_payment = Session::has('current_service_order_id_for_payment') && Session::get('current_service_order_id_for_payment') == $order->id;
            $has_pending_appointment_data = Session::has('pending_appointment_data');

            // Solo actualiza la orden si su estado es 'pending'
            if (strtolower($order->status) === 'pending') {
                $order->status = 'completed';
                $order->paypal_order_id = $paypalOrderId;
                $order->payer_id = $payerId;
                $order->payment_details = json_encode($response->result);
                $order->save();
                Log::info('ServiceOrder ' . $order->id . ' actualizada a "completed" en PaymentController@success.');

                // Enviar correo de confirmación de orden
                if ($order->user && $order->user->email) {
                    try {
                        Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
                        Log::info('Correo de confirmación de orden ' . $order->id . ' enviado a: ' . $order->user->email);
                    } catch (\Exception $mailEx) {
                        Log::error('Error al enviar correo de confirmación para la orden ' . $order->id . ': ' . $mailEx->getMessage());
                        Session::flash('warning', 'Tu pago fue procesado correctamente, pero no pudimos enviar el correo de confirmación.');
                    }
                }

                // **CAMBIO CRÍTICO AQUÍ:**
                // Decide a dónde redirigir en función de si hay una cita pendiente en la sesión.
                if ($is_new_appointment_payment && $has_pending_appointment_data) {
                    Log::info('Redirigiendo a completeBookingAfterPayment para finalizar la creación de la cita.');
                    Session::flash('payment_success_message', '¡Pago realizado con éxito! Agendando tu cita...');
                    return redirect()->route('client.citas.complete_booking');
                } else {
                    // Si no hay datos de cita pendiente, es una compra directa de servicio sin agendar cita aún
                    Log::info('Redirigiendo a showCompletedTransactionView o a create appointment (sin cita pendiente de crear).');
                    Session::flash('payment_success_message', '¡Pago realizado con éxito! Ahora puedes agendar tu cita.');
                    // Considera redirigir a `client.citas.create` con `preselected_service_order_id` aquí
                    // para que el usuario pueda agendar la cita inmediatamente con el servicio recién comprado.
                    return redirect()->route('client.citas.create', ['preselected_service_order_id' => $order->id]);
                    // O si solo quieres la vista de confirmación y que el usuario agende más tarde:
                    // return redirect()->route('payments.transaction_confirmed_view', ['service_order_id' => $order->id]);
                }

            } else if (strtolower($order->status) === 'completed') {
                Log::info('La orden local ' . $referenceId . ' ya estaba completada. Redirigiendo a página de confirmación.');
                Session::flash('info', 'Tu pago ya había sido confirmado previamente.');

                // Si ya estaba completada, verificamos si había una cita pendiente que debería haberse creado
                if ($is_new_appointment_payment && $has_pending_appointment_data) {
                    // Si ya estaba completada y había datos de cita, es posible que el callback se haya ejecutado
                    // pero la creación de la cita no se haya redirigido correctamente.
                    // En este caso, tratamos de forzar la creación de la cita si aún no existe.
                    $existingAppointment = Appointment::where('service_order_id', $order->id)->first();
                    if (!$existingAppointment) {
                         Log::warning('Orden ' . $order->id . ' ya completada, pero no hay cita asociada. Intentando forzar creación de cita.');
                        // Limpia la sesión de todos modos para evitar problemas futuros con datos antiguos
                        Session::forget('current_service_order_id_for_payment');
                        Session::forget('pending_appointment_data');
                        return redirect()->route('client.citas.complete_booking_after_payment'); // Reintenta el proceso de creación de cita
                    } else {
                        // Si ya está vinculada, simplemente redirige a las citas.
                        return redirect()->route('client.citas.index')->with('info', 'Tu pago ya había sido confirmado y tu cita ya está agendada.');
                    }
                } else {
                    // Si no había datos de cita pendiente, o no es un flujo de cita, redirige a la confirmación de la orden.
                    return redirect()->route('payments.transaction_confirmed_view', ['service_order_id' => $order->id]);
                }

            } else {
                Log::warning('Orden local ' . $referenceId . ' en estado inesperado (' . $order->status . ') después de un pago exitoso de PayPal.');
                Session::flash('error', 'El pago fue procesado en PayPal, pero la orden local está en un estado inesperado. Contacta a soporte.');
                return redirect()->route('client.home');
            }
        } else {
            Log::warning('Orden de PayPal NO COMPLETADA. Estado: ' . ($response->result->status ?? 'N/A') . ', ID de Orden de PayPal: ' . $token);
            Session::flash('error', 'El pago no se pudo completar. Estado: ' . ($response->result->status ?? 'Desconocido') . '. Por favor, intenta de nuevo o contacta a soporte.');
            return redirect()->route('payments.failed');
        }
    } catch (HttpException $ex) {
        Log::error('Error de PayPal (HttpException) al obtener/procesar la orden en success:', [
            'status' => $ex->statusCode,
            'message' => $ex->getMessage(),
            'details' => json_decode($ex->getMessage(), true)
        ]);
        Session::flash('error', 'Error al procesar el pago con PayPal: ' . $ex->getMessage());
        return redirect()->route('payments.failed');
    } catch (\Exception $ex) {
        Log::error('Error general al procesar la orden de PayPal en success:', [
            'message' => $ex->getMessage(),
            'trace' => $ex->getTraceAsString()
        ]);
        Session::flash('error', 'Ocurrió un error interno al procesar tu pago. Por favor, intenta de nuevo.');
        return redirect()->route('payments.failed');
    }
}


    public function cancel(Request $request)
    {
        Log::info('Método de cancelación de PayPal iniciado.', ['request_query' => $request->query()]);

        $localOrderId = $request->query('order_id_local');
        $token = $request->query('token');

        if ($localOrderId) {
            $order = ServiceOrder::find($localOrderId);
            if ($order) {
                if (strtolower($order->status) === 'pending') {
                    $order->update(['status' => 'CANCELLED']);
                    Log::info('Orden local ' . $localOrderId . ' marcada como CANCELADA.');
                    Session::flash('info', 'El pago de tu orden fue cancelado. Puedes intentar de nuevo.');
                } else {
                    Log::warning('La orden local ' . $localOrderId . ' no pudo ser marcada como cancelada porque no estaba en estado "pending". Estado actual: ' . $order->status);
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

        if ($request->expectsJson()) {
            return response()->json(['status' => 'CANCELLED', 'message' => 'Pago cancelado.']);
        }

        return redirect()->route('payments.failed');
    }
}
