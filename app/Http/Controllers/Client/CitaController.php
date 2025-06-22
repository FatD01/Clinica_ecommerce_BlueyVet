<?php

namespace App\Http\Controllers\Client;

use App\Models\VeterinarianSchedule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Mascota;
use App\Models\Service;
use App\Models\Cliente;
use App\Models\ServiceOrder;
use App\Models\Veterinarian;
use App\Models\ReprogrammingRequest; // ¡Importa el nuevo modelo!
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonPeriod; // Para generar los periodos de tiempo
class CitaController extends Controller
{
    protected function middleware(): array
    {
        return [
            'auth',
        ];
    }

    /**
     * Muestra una lista de las citas del cliente.
     * Es crucial cargar las relaciones correctas para que la vista index funcione bien.
     */
    public function index(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            Session::flash('error', 'No se encontró un perfil de cliente asociado a su cuenta. Por favor, complete su perfil.');
            return redirect()->route('dashboard');
        }

        $citas = Appointment::whereHas('mascota', function ($query) use ($cliente) {
            $query->where('cliente_id', $cliente->id); // Asegura que las mascotas pertenezcan al cliente
        })
            ->with([
                'mascota',
                'service',
                'veterinarian.user', // Asegúrate de que carga el usuario del veterinario
                'serviceOrder',
                // CAMBIO AQUÍ: Carga la última solicitud de reprogramación para esta cita
                'reprogrammingRequests' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(1);
                }
            ])
            ->orderBy('date', 'desc')
            ->get();

        Log::info('Citas obtenidas para el cliente ' . $cliente->id, ['count' => $citas->count(), 'citas_data' => $citas->toArray()]);

        $groupedAppointments = $citas->groupBy('mascota_id');

        Log::info('Citas agrupadas por mascota:', ['grouped_data' => $groupedAppointments->toArray()]);

        return view('client.citas.index', compact('groupedAppointments'));
    }

    /**
     * Muestra el formulario para crear una nueva cita.
     * Permite la preselección de un servicio si viene de una compra directa.
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
        $preselectedServiceOrderId = $request->query('preselected_service_order_id');

        if ($preselectedServiceOrderId) {
            $serviceOrder = ServiceOrder::find($preselectedServiceOrderId);

            if ($serviceOrder && $serviceOrder->user_id === Auth::id() && strtolower($serviceOrder->status) === 'completed') {
                $existingAppointment = Appointment::where('service_order_id', $serviceOrder->id)->first();
                if ($existingAppointment) {
                    Session::flash('warning', 'Esta compra de servicio ya tiene una cita agendada. Consulta tus citas.');
                    return redirect()->route('client.citas.index');
                }

                $preselectedService = $serviceOrder->service;
                Session::put('pending_service_order_id_to_link', $preselectedServiceOrderId);
                Log::info('Servicio preseleccionado de ServiceOrder comprada: ' . $preselectedService->name, ['order_id' => $preselectedServiceOrderId]);

                if (Session::has('info_appointment')) {
                    Session::flash('success', Session::get('info_appointment'));
                    Session::forget('info_appointment');
                }
            } else {
                Session::flash('error', 'La compra de servicio preseleccionada no es válida o no está pagada.');
                Log::warning('Intento de preseleccionar servicio con ServiceOrder inválida o impagada.', ['order_id' => $preselectedServiceOrderId]);
            }
        }

        $preselectedServiceIdFromPurchase = $request->query('preselected_service_id_from_purchase');
        if ($preselectedServiceIdFromPurchase && !$preselectedService) {
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

        $pendingServiceOrderIdToLink = Session::get('pending_service_order_id_to_link');
        $linkedServiceOrder = null;

        if ($pendingServiceOrderIdToLink) {
            $checkOrder = ServiceOrder::find($pendingServiceOrderIdToLink);
            if (
                $checkOrder &&
                $checkOrder->user_id === Auth::id() &&
                strtolower($checkOrder->status) === 'completed' &&
                $checkOrder->service_id == $selectedService->id
            ) {
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
            // Calcular la end_datetime (asumiendo una duración por defecto, o según el servicio)
            $startDateTime = Carbon::parse($validatedData['date']);
            // CAMBIO AQUÍ: Usa la duración del servicio para calcular end_datetime
            $durationMinutes = $selectedService->duration_minutes ?? 30; // Asumiendo una duración por defecto de 30 minutos si el servicio no la tiene
            $endDateTime = $startDateTime->copy()->addMinutes($durationMinutes);

            if ($linkedServiceOrder) {
                // Flujo 1: Cita agendada con un servicio PREVIAMENTE COMPRADO (ya pagado)
                $appointment = Appointment::create([
                    'mascota_id' => $validatedData['mascota_id'],
                    'veterinarian_id' => $validatedData['veterinarian_id'],
                    'date' => $startDateTime,
                    'end_datetime' => $endDateTime, // Agregado
                    'reason' => $validatedData['reason'],
                    'service_id' => $validatedData['service_id'],
                    'status' => 'pending', // La propia cita comienza como pendiente (a realizar)
                    'service_order_id' => $linkedServiceOrder->id, // Vincula a la orden de servicio ya pagada
                ]);

                Session::forget('pending_service_order_id_to_link');
                Session::flash('success', '¡Cita agendada exitosamente con tu servicio adquirido!');
                Log::info('Cita creada directamente y vinculada a ServiceOrder ' . $linkedServiceOrder->id . '.', ['appointment_id' => $appointment->id]);
                return redirect()->route('client.citas.index');
            } else {
                // Flujo 2: Agendar cita normalmente (requiere pago)
                $newServiceOrder = ServiceOrder::create([
                    'user_id' => Auth::id(),
                    'service_id' => $selectedService->id,
                    'status' => 'PENDING',
                    'amount' => $selectedService->price,
                    'currency' => config('app.locale_currency', 'PEN'),
                ]);

                Session::put('current_service_order_id_for_payment', $newServiceOrder->id);
                Session::put('pending_appointment_data', [
                    'mascota_id' => $validatedData['mascota_id'],
                    'veterinarian_id' => $validatedData['veterinarian_id'],
                    'date' => $startDateTime->toDateTimeString(), // Almacena como string para la sesión
                    'end_datetime' => $endDateTime->toDateTimeString(), // Almacena como string
                    'reason' => $validatedData['reason'],
                    'service_id' => $validatedData['service_id'],
                    'status' => 'pending', // Estado inicial
                    'service_order_id' => $newServiceOrder->id, // Para vincularlo después del pago
                ]);

                Log::info('Nueva ServiceOrder PENDIENTE creada para la cita, datos de la cita en la sesión.', [
                    'service_order_id' => $newServiceOrder->id,
                    'user_id' => Auth::id(),
                ]);

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
        $appointment->loadMissing('mascota');

        if (Auth::user()->cliente->id !== $appointment->mascota->cliente_id) {
            Log::warning('Intento de acceso no autorizado a cita.', [
                'user_id' => Auth::id(),
                'attempted_appointment_id' => $appointment->id,
                'mascota_cliente_id' => $appointment->mascota->cliente_id,
                'auth_cliente_id' => Auth::user()->cliente->id ?? 'N/A'
            ]);
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para ver los detalles de esta cita.');
        }

        return view('Client.citas.show', compact('appointment'));
    }

    public function getAvailableTimeSlots(Request $request)
    {
        Log::info('Request for available slots received.', $request->all());

        $veterinarianId = $request->input('veterinarian_id');
        $date = $request->input('date');
        $serviceId = $request->input('service_id');

        if (!$veterinarianId || !$date || !$serviceId) {
            Log::warning('Missing parameters for getAvailableTimeSlots.', $request->all());
            return response()->json(['error' => 'Parámetros incompletos.'], 400);
        }

        try {
            $selectedDate = Carbon::parse($date);
            $service = Service::findOrFail($serviceId);
            $veterinarian = Veterinarian::findOrFail($veterinarianId);

            $durationMinutes = $service->duration_minutes ?? 30;
            $dayName = strtolower($selectedDate->format('l'));

            Log::info("Fetching schedules for veterinarian_id: {$veterinarianId}, date: {$date}, day: {$dayName}");

            $workingSchedules = VeterinarianSchedule::where('veterinarian_id', $veterinarianId)
                ->whereJsonContains('day_of_week', $dayName)
                ->get();

            if ($workingSchedules->isEmpty()) {
                Log::info("No working schedules found for veterinarian {$veterinarianId} on {$dayName}.");
                return response()->json(['slots' => []]);
            }

            $availableSlots = [];

            foreach ($workingSchedules as $schedule) {
                $workStart = $selectedDate->copy()->setTimeFromTimeString($schedule->start_time->format('H:i:s'));
                $workEnd = $selectedDate->copy()->setTimeFromTimeString($schedule->end_time->format('H:i:s'));

                if ($workEnd->lte($workStart)) {
                    $workEnd->addDay();
                }

                Log::info("Processing schedule from {$workStart->toDateTimeString()} to {$workEnd->toDateTimeString()}");

                $period = CarbonPeriod::create($workStart, "{$durationMinutes} minutes", $workEnd);

                foreach ($period as $slotStart) {
                    $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

                    if ($slotEnd->gt($workEnd)) {
                        continue;
                    }

                    // Asegurarse de que el slot sea en el futuro inmediato (al menos 5 minutos desde ahora)
                    // Considerando la hora actual de Peru
                    if ($slotStart->lt(Carbon::now('America/Lima')->addMinutes(5))) { // Cita: 1
                        continue;
                    }

                    // Verificar superposición con citas existentes del veterinario
                    $isBooked = Appointment::where('veterinarian_id', $veterinarianId)
                        ->where(function ($query) use ($slotStart, $slotEnd) {
                            $query->where(function ($q) use ($slotStart, $slotEnd) {
                                $q->where('date', '>=', $slotStart)
                                    ->where('date', '<', $slotEnd);
                            })->orWhere(function ($q) use ($slotStart, $slotEnd) {
                                $q->where('end_datetime', '>', $slotStart)
                                    ->where('end_datetime', '<=', $slotEnd);
                            })->orWhere(function ($q) use ($slotStart, $slotEnd) {
                                $q->where('date', '<=', $slotStart)
                                    ->where('end_datetime', '>=', $slotEnd);
                            });
                        })
                        ->whereNotIn('status', ['cancelled', 'rejected', 'reprogramming_rejected_by_client', 'reprogrammed'])
                        ->exists();

                    if ($isBooked) {
                        Log::info("Slot {$slotStart->format('H:i')} - {$slotEnd->format('H:i')} is booked.");
                        continue;
                    }


                    // *** ESTA ES LA SECCIÓN QUE ELIMINASTE/COMENTASTE DE SCHEDULEBLOCK ***
                    // Si en el futuro lo implementas, lo volverías a añadir aquí.

                    $availableSlots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                    ];
                }
            }

            Log::info("Found " . count($availableSlots) . " available slots.");
            return response()->json(['slots' => $availableSlots]);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableTimeSlots: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Error interno del servidor al obtener horarios.'], 500);
        }
    }

    /**
     * Maneja el callback de pago exitoso (llamado después de que PayPal confirma el pago
     * y PaymentController@success ha actualizado la ServiceOrder a COMPLETED).
     */
    public function completeBookingAfterPayment(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::info('Iniciando CitaController@completeBookingAfterPayment.');

        $currentServiceOrderId = Session::get('current_service_order_id_for_payment');
        $pendingAppointmentData = Session::get('pending_appointment_data');

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
        if (strtolower($serviceOrder->status) !== 'completed') {
            Log::error('ServiceOrder no marcada como "completed" por PaymentController al intentar crear la cita. Estado actual: ' . $serviceOrder->status, ['service_order_id' => $serviceOrder->id]);
            return redirect()->route('client.citas.create')->with('error', 'El pago no fue confirmado para esta orden. Por favor, verifica el estado de tu pago o intenta de nuevo.');
        }
        if (Appointment::where('service_order_id', $serviceOrder->id)->exists()) {
            Log::warning('ServiceOrder ' . $serviceOrder->id . ' ya tiene una cita asociada. Posible reintento de callback.');
            return redirect()->route('client.citas.index')->with('info', '¡Tu cita ya ha sido agendada con éxito!');
        }
        Log::info('Datos para crear cita en completeBookingAfterPayment:', $pendingAppointmentData);
        try {
            // Asegurarse de que las fechas se conviertan de string a objetos Carbon
            $pendingAppointmentData['date'] = Carbon::parse($pendingAppointmentData['date']);
            $pendingAppointmentData['end_datetime'] = Carbon::parse($pendingAppointmentData['end_datetime']);

            $appointment = Appointment::create(array_merge($pendingAppointmentData, [
                'service_order_id' => $serviceOrder->id, // ¡VINCULA LA CITA A LA ORDEN DE SERVICIO PAGADA!
            ]));
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
    /*
    |--------------------------------------------------------------------------
    | Métodos para la Reprogramación de Citas
    |--------------------------------------------------------------------------
    */

    public function showReprogrammingForm(Appointment $appointment): View|\Illuminate\Http\RedirectResponse
    {
        // Validar que la cita pertenezca al cliente autenticado
        $client = Auth::user()->cliente;
        if (!$client || $appointment->mascota->cliente_id !== $client->id) { // Usamos cliente_id
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para reprogramar esta cita.');
        }

        // Si ya hay una solicitud de reprogramación 'activa' para esta cita,
        // redirigir al estado de la solicitud para evitar duplicados.
        // Los estados 'pending_client_confirmation' y 'pending_veterinarian_confirmation' indican una solicitud en curso.
        $activeReprogrammingRequest = ReprogrammingRequest::where('appointment_id', $appointment->id)
            ->whereIn('status', ['pending_client_confirmation', 'pending_veterinarian_confirmation'])
            ->first();
        if ($activeReprogrammingRequest) {
            // CAMBIO AQUÍ: Usamos el nombre de ruta correcto 'client.citas.reprogram.status'
            return redirect()->route('client.citas.reprogram.status', $appointment->id)->with('info', 'Ya existe una solicitud de reprogramación pendiente o en curso para esta cita.');
        }

        // Cargar las relaciones necesarias para mostrar la información de la cita original en el formulario
        // CAMBIO AQUÍ: Asegurarse de cargar 'service' para tener la duración disponible
        $appointment->load('mascota', 'veterinarian.user', 'service');

        return view('client.citas.reprogram_form', compact('appointment'));
    }

    /**
     * Store a new reprogramming request.
     * Almacena una nueva solicitud de reprogramación en la base de datos.
     */
    public function storeReprogrammingRequest(Request $request, Appointment $appointment): \Illuminate\Http\RedirectResponse
    {
        $client = Auth::user()->cliente;
        if (!$client || $appointment->mascota->cliente_id !== $client->id) {
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para reprogramar esta cita.');
        }

        // CAMBIO AQUÍ: Carga la relación 'service' si no está ya cargada para asegurar que duration_minutes esté disponible
        $appointment->loadMissing('service');

        // Validación de los datos de la propuesta
        $request->validate([
            'proposed_start_date_time' => 'required|date|after:now', // La nueva fecha y hora debe ser en el futuro
            // CAMBIO AQUÍ: ¡Eliminamos la validación de 'proposed_end_date_time' del request!
            'reprogramming_reason' => 'required|string|max:500',
        ]);

        try {
            $proposedStart = Carbon::parse($request->proposed_start_date_time);

            // CAMBIO AQUÍ: Calcula proposed_end_date_time usando la duración del servicio de la cita original
            $durationMinutes = $appointment->service->duration_minutes ?? 30; // Obtiene la duración del servicio o usa 30 minutos por defecto
            $proposedEnd = $proposedStart->copy()->addMinutes($durationMinutes);

            // 1. Crear la solicitud de reprogramación
            $reprogrammingRequest = ReprogrammingRequest::create([
                'appointment_id' => $appointment->id,
                'client_id' => $client->id,
                'veterinarian_id' => $appointment->veterinarian_id,
                'requester_type' => 'client', // El cliente es quien inicia la solicitud
                'requester_user_id' => Auth::id(),
                'proposed_start_date_time' => $proposedStart,
                'proposed_end_date_time' => $proposedEnd, // Usamos el valor calculado
                'reprogramming_reason' => $request->reprogramming_reason,
                'client_confirmed' => true, // El cliente confirma su propia propuesta al enviarla
                'client_confirmed_at' => Carbon::now(),
                'veterinarian_confirmed' => false, // Todavía no ha confirmado el veterinario
                'veterinarian_confirmed_at' => null,
                'status' => 'pending_veterinarian_confirmation', // Esperando confirmación del veterinario
                'admin_notes' => null,
            ]);

            // 2. Actualizar el estado de la cita original para reflejar que está en proceso de reprogramación
            $appointment->status = 'pending_reprogramming';
            $appointment->save();

            Log::info('Solicitud de reprogramación creada por cliente.', ['request_id' => $reprogrammingRequest->id, 'appointment_id' => $appointment->id]);

            // CAMBIO AQUÍ: Usamos el nombre de ruta correcto 'client.citas.reprogram.status'
            return redirect()->route('client.citas.reprogram.status', $appointment->id)->with('success', 'Tu solicitud de reprogramación ha sido enviada con éxito. El veterinario la revisará pronto.');
        } catch (\Exception $e) {
            Log::error('Error al procesar solicitud de reprogramación en CitaController@storeReprogrammingRequest: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'appointment_id' => $appointment->id
            ]);
            return redirect()->back()->withInput()->with('error', 'Hubo un error al procesar tu solicitud de reprogramación: ' . $e->getMessage());
        }
    }

    /**
     * Display the status of a specific reprogramming request for an appointment.
     * Muestra el estado de la solicitud de reprogramación de una cita específica.
     */
    public function showReprogrammingStatus(Appointment $appointment): View|\Illuminate\Http\RedirectResponse
    {
        $client = Auth::user()->cliente;
        if (!$client || $appointment->mascota->cliente_id !== $client->id) {
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para ver el estado de esta cita.');
        }

        // Buscar la solicitud de reprogramación más reciente para esta cita
        // que el cliente inició o que está esperando su acción.
        $reprogrammingRequest = ReprogrammingRequest::where('appointment_id', $appointment->id)
            ->where(function ($query) {
                $query->where('requester_type', 'client') // La solicitud fue iniciada por el cliente
                    ->whereIn('status', ['pending_veterinarian_confirmation', 'accepted_by_both', 'applied', 'rejected_by_veterinarian', 'cancelled_by_request', 'obsolete_by_new_proposal'])
                    ->orWhere(function ($q) { // O una solicitud iniciada por el veterinario esperando confirmación del cliente
                        $q->where('requester_type', 'veterinarian')
                            ->where('status', 'pending_client_confirmation');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->with(['appointment.mascota', 'appointment.veterinarian.user']) // Carga relaciones necesarias para la vista
            ->first();


        if (!$reprogrammingRequest) {
            return redirect()->route('client.citas.index')->with('info', 'No se encontró una solicitud de reprogramación activa para esta cita. Puedes intentar reprogramarla si es posible.');
        }

        return view('client.citas.reprogram_status', compact('reprogrammingRequest'));
    }

    /**
     * Permite al cliente confirmar (aceptar o rechazar) una propuesta de reprogramación
     * iniciada por el veterinario.
     */
    public function confirmReprogrammingRequest(Request $request, ReprogrammingRequest $reprogrammingRequest): \Illuminate\Http\RedirectResponse
    {
        $client = Auth::user()->cliente;

        // Verificar que la solicitud pertenezca al cliente o esté dirigida a él
        if (!$client || $reprogrammingRequest->client_id !== $client->id) {
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para interactuar con esta solicitud de reprogramación.');
        }

        // Solo permitir acción si el estado es 'pending_client_confirmation'
        if ($reprogrammingRequest->status !== 'pending_client_confirmation') {
            // CAMBIO AQUÍ: Usamos el nombre de ruta correcto 'client.citas.reprogram.status'
            return redirect()->route('client.citas.reprogram.status', $reprogrammingRequest->appointment_id)->with('info', 'Esta solicitud ya no está pendiente de tu confirmación.');
        }

        $action = $request->input('action'); // 'accept' o 'reject'

        try {
            if ($action === 'accept') {
                $reprogrammingRequest->update([
                    'client_confirmed' => true,
                    'client_confirmed_at' => Carbon::now(),
                    'status' => 'accepted_by_both', // Ambos han confirmado
                ]);

                // Opcional: Aquí puedes disparar un evento para que un Job o Listener
                // actualice la cita original con la nueva fecha.
                // Por ejemplo, `dispatch(new ApplyReprogramming($reprogrammingRequest));`
                // O puedes hacerlo directamente aquí si es simple:
                $reprogrammingRequest->appointment->update([
                    'date' => $reprogrammingRequest->proposed_start_date_time,
                    'end_datetime' => $reprogrammingRequest->proposed_end_date_time,
                    'status' => 'reprogrammed', // Estado de la cita actualizado
                ]);
                $reprogrammingRequest->update(['status' => 'applied']); // Marca la solicitud como aplicada

                Log::info('Cliente aceptó solicitud de reprogramación.', ['request_id' => $reprogrammingRequest->id]);
                // CAMBIO AQUÍ: Usamos el nombre de ruta correcto 'client.citas.reprogram.status'
                return redirect()->route('client.citas.reprogram.status', $reprogrammingRequest->appointment_id)->with('success', '¡Has aceptado la nueva fecha! La cita ha sido reprogramada.');
            } elseif ($action === 'reject') {
                $reprogrammingRequest->update([
                    'client_confirmed' => false,
                    'client_confirmed_at' => Carbon::now(),
                    'status' => 'rejected_by_client', // Cliente rechazó
                ]);

                // Opcional: Aquí podrías querer revertir el estado de la cita original a 'pending' o 'confirmed'
                // O dejarla como 'pending_reprogramming_rejected' para que el veterinario la vea
                $reprogrammingRequest->appointment->update([
                    'status' => 'reprogramming_rejected_by_client', // Nuevo estado o similar
                ]);

                Log::info('Cliente rechazó solicitud de reprogramación.', ['request_id' => $reprogrammingRequest->id]);
                // CAMBIO AQUÍ: Usamos el nombre de ruta correcto 'client.citas.reprogram.status'
                return redirect()->route('client.citas.reprogram.status', $reprogrammingRequest->appointment_id)->with('info', 'Has rechazado la propuesta de reprogramación.');
            }

            return redirect()->back()->with('error', 'Acción inválida.');
        } catch (\Exception $e) {
            Log::error('Error al confirmar/rechazar solicitud de reprogramación por cliente: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_id' => $reprogrammingRequest->id,
                'action' => $action
            ]);
            return redirect()->back()->with('error', 'Hubo un error al procesar tu respuesta: ' . $e->getMessage());
        }
    }

    // Métodos existentes (citasAgendadas, verMascotas) no se modifican ya que son para el lado del veterinario.
    // Aunque el nombre del controlador es Client\CitaController, estos métodos parecen ser de un Veterinarian\CitaController.
    // Esto podría causar confusión o problemas de permisos si no están correctamente manejados por middleware.
    // Te recomiendo revisar la ubicación de estos métodos si están destinados solo al veterinario.
    public function citasAgendadas(Request $request)
    {
        $veterinario = Auth::user()->veterinarian;

        // ... (resto de tu código de citasAgendadas)
        // No se cambia, asumiendo que es para el rol de veterinario y esta función se llamará desde otra ruta
        // o que tu sistema de permisos lo maneja.

        // Simplemente copio el cuerpo para que tu archivo no se modifique
        $status = $request->input('status', 'pending');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $mascotaId = $request->input('mascota_id');

        $clientes = Cliente::whereHas('mascotas.appointments', function ($query) use ($veterinario, $status, $desde, $hasta, $mascotaId) {
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
            if ($mascotaId) {
                $query->where('mascota_id', $mascotaId);
            }
        })
            ->with('user')
            ->get();

        $citas = Appointment::with(['mascota.cliente', 'service'])
            ->where('veterinarian_id', $veterinario->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($desde, fn($q) => $q->whereDate('date', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('date', '<=', $hasta))
            ->when($mascotaId, fn($q) => $q->where('mascota_id', $mascotaId))
            ->get();

        $numeroCitasMascota = null;
        if ($mascotaId) {
            $numeroCitasMascota = $citas->count();
        }

        $mascotas = \App\Models\Mascota::whereHas('appointments', function ($q) use ($veterinario) {
            $q->where('veterinarian_id', $veterinario->id);
        })->get();

        $eventos = [];
        foreach ($citas as $cita) {
            $cliente = $cita->mascota->cliente ?? null;
            $mascota = $cita->mascota ?? null;
            $servicio = $cita->service ?? null;

            if ($cliente && $mascota) {
                $eventos[] = [
                    'title' => $mascota->name . ' - ' . $cliente->user->name, // Usar user->name para el cliente
                    'start' => $cita->date,
                    'color' => '#0d6efd',
                    'extendedProps' => [
                        'cliente' => $cliente->user->name . ' ' . $cliente->user->last_name, // Usar user->name y user->last_name
                        'email' => $cliente->user->email ?? 'No disponible',
                        'telefono' => $cliente->telefono ?? 'No registrado',
                        'direccion' => $cliente->direccion ?? 'No registrada',
                        'servicio' => $servicio->name ?? 'Servicio no especificado',
                        'verMascotasUrl' => route('ver.mascotas', [
                            'id' => $cliente->id,
                            'cita' => $cita->id
                        ])
                    ]
                ];
            }
        }

        return view('citasagendadas', compact('clientes', 'eventos', 'mascotas', 'mascotaId', 'numeroCitasMascota'));
    }

    public function verMascotas($id, $citaId)
    {
        $cita = Appointment::with('mascota.cliente')
            ->where('id', $citaId)
            ->first();

        if (!$cita || !$cita->mascota || $cita->mascota->cliente->id != $id) {
            abort(404, 'Cita no encontrada o no pertenece a este cliente');
        }

        $mascota = $cita->mascota;
        $cliente = $mascota->cliente;

        return view('vermascotas', [
            'cliente' => $cliente,
            'mascotas' => collect([$mascota]),
            'citaEspecifica' => $cita,
        ]);
    }

    /**
     * Nuevo método para obtener los slots de tiempo disponibles de un veterinario para una fecha y servicio específicos.
     * Este método se llama vía AJAX desde la vista.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
}
