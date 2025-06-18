<?php

namespace App\Http\Controllers\Client;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Mascota;
use App\Models\Service;
use App\Models\Cliente;
use App\Models\ServiceOrder;
use App\Models\Veterinarian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;



class CitaController extends Controller
{


    // Muestra los clientes que tienen citas con el veterinario autenticado (con filtros)
    public function citasAgendadas(Request $request)
    {
        $veterinario = Auth::user()->veterinarian;

        $status = $request->input('status');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        // Obtener clientes con al menos una mascota con cita con este veterinario y filtros
        $clientes = Cliente::whereHas('mascotas.appointments', function ($query) use ($veterinario, $status, $desde, $hasta) {
            $query->where('veterinarian_id', $veterinario->id);

            if ($status) {
                $query->where('status', $status);
            }

            if ($desde) {
                $query->whereDate('date', '>=', $desde);
            }

            if ($hasta) {
                $query->whereDate('date', '<=', $hasta);
            }
        })
        ->with('user')
        ->get();

        return view('citasagendadas', compact('clientes'));
    }

    // Muestra las mascotas de un cliente que tienen citas pendientes con el veterinario autenticado
    public function verMascotas($id)
{
    $veterinario = Auth::user()->veterinarian;

    $cliente = Cliente::find($id);
    if (!$cliente) {
        abort(404, 'Cliente no encontrado');
    }

    // Obtener mascotas del cliente que tienen citas pendientes con este veterinario
    $mascotas = Mascota::where('cliente_id', $cliente->id)
        ->whereHas('appointments', function ($query) use ($veterinario) {
            $query->where('veterinarian_id', $veterinario->id)
                  ->where('status', 'pending');
        })
        ->with(['appointments' => function ($query) use ($veterinario) {
            $query->where('veterinarian_id', $veterinario->id)
                  ->where('status', 'pending')
                  ->orderBy('date', 'asc'); // opcional: ordena por fecha
        }])
        ->get();

    return view('vermascotas', compact('cliente', 'mascotas'));
}








    protected function middleware(): array
    {
        return [
            'auth',
        ];
    }
public function index(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $cliente = Auth::user()->cliente;

    if (!$cliente) {
        Session::flash('error', 'No se encontró un perfil de cliente asociado a su cuenta. Por favor, complete su perfil.');
        return redirect()->route('dashboard');
    }

    $citas = Appointment::whereHas('mascota', function ($query) use ($cliente) { // Uso $cliente directamente
        $query->where('cliente_id', $cliente->id); // Asegura que las mascotas pertenezcan al cliente
    })
    ->with(['mascota', 'service', 'veterinarian.user', 'serviceOrder'])
    ->orderBy('date', 'desc')
    ->get();

    // **Añade esto para depurar la colección de citas:**
    Log::info('Citas obtenidas para el cliente ' . $cliente->id, ['count' => $citas->count(), 'citas_data' => $citas->toArray()]);

    $groupedAppointments = $citas->groupBy('mascota_id');

    // **Añade esto para depurar el agrupamiento:**
    Log::info('Citas agrupadas por mascota:', ['grouped_data' => $groupedAppointments->toArray()]);

    return view('client.citas.index', compact('groupedAppointments'));
}
    /**
     * Muestra el formulario para crear una nueva cita.
     * Permite la preselección de un servicio si viene de una compra directa.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            Session::flash('error', 'Debe tener un perfil de cliente para agendar citas. Por favor, complete su perfil.');
            return redirect()->route('dashboard');
        }

        $mascotas = $cliente->mascotas;
        $allServices = Service::all();
        $veterinarians = Veterinarian::with('user')->get();

        $preselectedService = null;
        // Este parámetro viene de PaymentController@success después de una compra de servicio
        $preselectedServiceOrderId = $request->query('preselected_service_order_id');

        if ($preselectedServiceOrderId) {
            $serviceOrder = ServiceOrder::find($preselectedServiceOrderId);

            // Verifica que la orden de servicio exista, pertenezca al usuario y esté pagada
            if ($serviceOrder && $serviceOrder->user_id === Auth::id() && strtolower($serviceOrder->status) === 'completed') {
                // Comprueba si la orden ya tiene una cita asociada (para evitar doble reserva)
                $existingAppointment = Appointment::where('service_order_id', $serviceOrder->id)->first();
                if ($existingAppointment) {
                    Session::flash('warning', 'Esta compra de servicio ya tiene una cita agendada. Consulta tus citas.');
                    return redirect()->route('client.citas.index');
                }

                $preselectedService = $serviceOrder->service;
                // Almacena el ID de la ServiceOrder en la sesión para vincularlo en el método store
                Session::put('pending_service_order_id_to_link', $preselectedServiceOrderId);
                Log::info('Servicio preseleccionado de ServiceOrder comprada: ' . $preselectedService->name, ['order_id' => $preselectedServiceOrderId]);

                // Muestra el mensaje de éxito de la compra
                if (Session::has('info_appointment')) {
                    Session::flash('success', Session::get('info_appointment'));
                    Session::forget('info_appointment'); // Limpiar para que no se muestre de nuevo
                }

            } else {
                Session::flash('error', 'La compra de servicio preseleccionada no es válida o no está pagada.');
                Log::warning('Intento de preseleccionar servicio con ServiceOrder inválida o impagada.', ['order_id' => $preselectedServiceOrderId]);
            }
        }

        // Este parámetro viene del botón "Agendar Cita con este Servicio" en la página de índice de servicios
        $preselectedServiceIdFromPurchase = $request->query('preselected_service_id_from_purchase');
        if ($preselectedServiceIdFromPurchase && !$preselectedService) { // Solo se establece si no se preseleccionó ninguna ServiceOrder
             $preselectedService = Service::find($preselectedServiceIdFromPurchase);
             if (!$preselectedService) {
                 Session::flash('error', 'El servicio preseleccionado no es válido.');
                 Log::warning('ID de servicio inválido proporcionado para preselección en la creación de citas.', ['service_id' => $preselectedServiceIdFromPurchase]);
             }
        }


        return view('client.citas.create', compact('mascotas', 'allServices', 'veterinarians', 'preselectedService'));
    }

    /**
     * Almacena una nueva cita en la base de datos.
     * Si hay una ServiceOrder pagada preseleccionada en la sesión, la vincula.
     * De lo contrario, crea una nueva ServiceOrder y redirige a la pasarela de pago.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            return redirect()->back()->with('error', 'No se encontró el perfil de cliente asociado. Por favor, intente de nuevo.');
        }

        $validatedData = $request->validate([
            'mascota_id' => [
                'required',
                'exists:mascotas,id',
                Rule::exists('mascotas', 'id')->where(function ($query) use ($cliente) {
                    $query->where('cliente_id', $cliente->id);
                }),
            ],
            'veterinarian_id' => 'required|exists:veterinarians,id',
            'date' => 'required|date|after_or_equal:now',
            'reason' => 'nullable|string|max:255',
            'service_id' => 'required|exists:services,id',
        ]);

        $selectedService = Service::findOrFail($validatedData['service_id']);

        // Recuperar el ID de la ServiceOrder si proviene de una compra directa de servicio
        $pendingServiceOrderIdToLink = Session::get('pending_service_order_id_to_link');
        $linkedServiceOrder = null;

        if ($pendingServiceOrderIdToLink) {
            $checkOrder = ServiceOrder::find($pendingServiceOrderIdToLink);
            // Verifica que la ServiceOrder exista, pertenezca al usuario, esté COMPLETED y el servicio coincida
            if ($checkOrder &&
                $checkOrder->user_id === Auth::id() &&
                strtolower($checkOrder->status) === 'completed' &&
                $checkOrder->service_id == $selectedService->id)
            {
                // Verifica que esta ServiceOrder no esté ya vinculada a otra cita
                if (!Appointment::where('service_order_id', $checkOrder->id)->exists()) {
                    $linkedServiceOrder = $checkOrder;
                    Log::info('ServiceOrder ' . $pendingServiceOrderIdToLink . ' preseleccionada y válida para vincular a la cita.');
                } else {
                    Log::warning('ServiceOrder ' . $pendingServiceOrderIdToLink . ' ya vinculada a una cita. Ignorando preselección.');
                    Session::flash('warning', 'La compra de servicio seleccionada ya tiene una cita agendada.');
                }
            } else {
                Log::warning('ServiceOrder preseleccionada inválida o impagada/desajuste de servicio. Ignorando preselección.', ['order_id' => $pendingServiceOrderIdToLink]);
            }
        }

        try {
            if ($linkedServiceOrder) {
                // Flujo 1: Cita agendada con un servicio PREVIAMENTE COMPRADO (ya pagado)
                $appointment = Appointment::create([
                    'mascota_id' => $validatedData['mascota_id'],
                    'veterinarian_id' => $validatedData['veterinarian_id'],
                    'date' => Carbon::parse($validatedData['date']),
                    'reason' => $validatedData['reason'],
                    'service_id' => $validatedData['service_id'],
                    'status' => 'pending', // La propia cita comienza como pendiente (a realizar)
                    'service_order_id' => $linkedServiceOrder->id, // Vincula a la orden de servicio ya pagada
                ]);

                // Limpia la sesión después de usar la ServiceOrder preseleccionada
                Session::forget('pending_service_order_id_to_link');
                Session::flash('success', '¡Cita agendada exitosamente con tu servicio adquirido!');
                Log::info('Cita creada directamente y vinculada a ServiceOrder ' . $linkedServiceOrder->id . '.', ['appointment_id' => $appointment->id]);
                return redirect()->route('client.citas.index');

            } else {
                // Flujo 2: Agendar cita normalmente (requiere pago)
                // Crea una NUEVA ServiceOrder PENDIENTE para esta transacción de cita
                $newServiceOrder = ServiceOrder::create([
                    'user_id' => Auth::id(),
                    'service_id' => $selectedService->id,
                    'status' => 'PENDING',
                    'amount' => $selectedService->price,
                    'currency' => config('app.locale_currency', 'PEN'), // Usa APP_LOCALE_CURRENCY de .env
                ]);

                // Almacena los datos de la cita y el ID de la ServiceOrder en la sesión
                // para ser completado después del pago
                Session::put('current_service_order_id_for_payment', $newServiceOrder->id);
                Session::put('pending_appointment_data', $validatedData); // Guarda todos los datos de la cita

                Log::info('Nueva ServiceOrder PENDIENTE creada para la cita, datos de la cita en la sesión.', [
                    'service_order_id' => $newServiceOrder->id,
                    'user_id' => Auth::id(),
                ]);

                // Redirige a la pasarela de pago (PayPal)
                return redirect()->route('payments.show_checkout_page', ['serviceOrderId' => $newServiceOrder->id])
                                 ->with('info', 'Por favor, completa el pago para confirmar tu cita.');
            }

        } catch (\Exception $e) {
            Log::error('Error al procesar la cita en CitaController@store: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validatedData
            ]);
            return redirect()->back()->with('error', 'Hubo un error al procesar tu cita. Inténtalo de nuevo.');
        }
    }

    public function show(Appointment $appointment): View|\Illuminate\Http\RedirectResponse
    {
        // Seguridad: Asegúrate de que la cita pertenece al cliente autenticado
        // o a una mascota de ese cliente.
        // Carga la relación 'mascota' si no está ya cargada
        $appointment->loadMissing('mascota'); 

        if (Auth::user()->cliente->id !== $appointment->mascota->cliente_id) {
            Log::warning('Intento de acceso no autorizado a cita.', [
                'user_id' => Auth::id(),
                'attempted_appointment_id' => $appointment->id,
                'mascota_cliente_id' => $appointment->mascota->cliente_id,
                'auth_cliente_id' => Auth::user()->cliente->id ?? 'N/A'
            ]);
            return redirect()->route('Client.citas.index')->with('error', 'No tienes permiso para ver los detalles de esta cita.');
        }

        // Si todo está bien, pasa la cita a la vista
        return view('Client.citas.show', compact('appointment'));
    }


    /**
     * Maneja el callback de pago exitoso (llamado después de que PayPal confirma el pago
     * y PaymentController@success ha actualizado la ServiceOrder a COMPLETED).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeBookingAfterPayment(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::info('Iniciando CitaController@completeBookingAfterPayment.');

        // Recupera el ID de la ServiceOrder que se esperaba pagar
        $currentServiceOrderId = Session::get('current_service_order_id_for_payment');
        $pendingAppointmentData = Session::get('pending_appointment_data');

        // Limpiar la sesión inmediatamente para evitar reintentos accidentales
        Session::forget('current_service_order_id_for_payment');
        Session::forget('pending_appointment_data');

        if (!$currentServiceOrderId || !$pendingAppointmentData) {
            Log::error('Faltan datos clave en la sesión para completar la reserva después del pago. Sesión expirada o flujo incorrecto.');
            return redirect()->route('client.citas.create')->with('error', 'No se pudo completar la reserva. Datos pendientes no encontrados o sesión expirada. Intenta de nuevo.');
        }

        $serviceOrder = ServiceOrder::find($currentServiceOrderId);

        if (!$serviceOrder) {
            Log::error('ServiceOrder no encontrada para el ID en sesión en completeBookingAfterPayment.', ['current_service_order_id' => $currentServiceOrderId]);
            return redirect()->route('client.citas.create')->with('error', 'La orden de servicio no fue encontrada. Intenta de nuevo.');
        }

        // Esta es la verificación CRÍTICA: la ServiceOrder DEBE estar 'COMPLETED'
        if (strtolower($serviceOrder->status) !== 'completed') {
            Log::error('ServiceOrder no marcada como "completed" por PaymentController al intentar crear la cita. Estado actual: ' . $serviceOrder->status, ['service_order_id' => $serviceOrder->id]);
            return redirect()->route('client.citas.create')->with('error', 'El pago no fue confirmado para esta orden. Por favor, verifica el estado de tu pago o intenta de nuevo.');
        }

        // Si la ServiceOrder ya tiene una cita, es un reintento o un procesamiento doble
        if (Appointment::where('service_order_id', $serviceOrder->id)->exists()) {
             Log::warning('ServiceOrder ' . $serviceOrder->id . ' ya tiene una cita asociada. Posible reintento de callback.');
             return redirect()->route('client.citas.index')->with('info', '¡Tu cita ya ha sido agendada con éxito!');
        }
         Log::info('Datos para crear cita en completeBookingAfterPayment:', $pendingAppointmentData);

        try {
            // Crea la cita, vinculándola a ESTA ServiceOrder específica
            $appointment = Appointment::create([
                'mascota_id' => $pendingAppointmentData['mascota_id'],
                'veterinarian_id' => $pendingAppointmentData['veterinarian_id'],
                'date' => Carbon::parse($pendingAppointmentData['date']),
                'reason' => $pendingAppointmentData['reason'],
                'service_id' => $pendingAppointmentData['service_id'],
                'status' => 'pending',
                'service_order_id' => $serviceOrder->id, // ¡VINCULA LA CITA A LA ORDEN DE SERVICIO PAGADA!
            ]);

            Log::info('Cita creada exitosamente y vinculada a ServiceOrder:', ['appointment_id' => $appointment->id, 'service_order_id' => $serviceOrder->id]);

            return redirect()->route('client.citas.index')->with('success', '¡Pago procesado y cita agendada exitosamente!');

        } catch (\Exception $e) {
            Log::error('Error al crear la cita en CitaController@completeBookingAfterPayment: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'service_order_id' => $currentServiceOrderId
            ]);
            return redirect()->route('client.citas.create')->with('error', 'Hubo un error al agendar tu cita después del pago. Por favor, contacta a soporte.');
        }
    }
}