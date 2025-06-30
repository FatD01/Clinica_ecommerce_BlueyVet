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
use App\Models\Post;

use App\Models\ReprogrammingRequest; // ¡Importa el nuevo modelo!
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonPeriod; // Para generar los periodos de tiempo
use Illuminate\Support\Facades\DB; //para manejar transacciones

use App\Notifications\ReprogrammingRequestStatusUpdate; // La nueva notificación


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
     */ public function create(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            Session::flash('error', 'Debe tener un perfil de cliente para agendar citas. Por favor, complete su perfil.');
            return redirect()->route('dashboard');
        }

        // Obtener las mascotas del cliente actual
        $mascotas = $cliente->mascotas;

        // Obtener todos los servicios que tienen veterinarios asociados a sus especialidades
        $allServices = Service::whereHas('specialties.veterinarians')
            ->with('specialties')
            ->get();

        // Los veterinarios inicialmente estarán vacíos, se cargarán dinámicamente con AJAX
        $veterinarians = collect(); // Se mantiene como collect() ya que se carga vía AJAX

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

        // Obtener los posts recientes.
        // Si tienes un View Composer configurado para 'client.welcome' (o tu layout base),
        // y tu vista `client.citas.create` extiende o incluye ese layout/vista,
        // puedes eliminar la siguiente línea, ya que el View Composer ya se encargará de ello.
        // De lo contrario, esta línea es necesaria.
        $recentPosts = Post::orderBy('created_at', 'desc')->take(3)->get();


        // Retornar la vista con TODAS las variables necesarias en una sola sentencia.
        return view('client.citas.create', compact('mascotas', 'allServices', 'veterinarians', 'preselectedService', 'recentPosts'));
    }

    public function getVeterinariansByService(Request $request)
    {
        $serviceId = $request->input('service_id');
        Log::info('Solicitud AJAX para getVeterinariansByService. Service ID:', ['service_id' => $serviceId]);

        if (!$serviceId) {
            Log::warning('getVeterinariansByService: No se proporcionó service_id.');
            return response()->json([], 400); // Bad request si no hay service_id
        }

        $service = Service::with('specialties')->find($serviceId);

        if (!$service) {
            Log::warning('getVeterinariansByService: Servicio no encontrado para ID:', ['service_id' => $serviceId]);
            return response()->json([], 404); // Servicio no encontrado
        }

        // Obtener los IDs de las especialidades requeridas por el servicio
        $requiredSpecialtyIds = $service->specialties->pluck('id');
        Log::info('Especialidades requeridas para el servicio:', ['specialty_ids' => $requiredSpecialtyIds->toArray()]);

        // Buscar veterinarios que tengan AL MENOS UNA de las especialidades requeridas
        // Asegúrate de que Veterinarian tiene una relación 'specialties' (muchos a muchos)
        // y una relación 'user' (uno a uno)
        $veterinarians = Veterinarian::whereHas('specialties', function ($query) use ($requiredSpecialtyIds) {
            $query->whereIn('specialties.id', $requiredSpecialtyIds);
        })->with('user', 'specialties')->get(); // Cargar ambas relaciones

        // Formatear los datos para la respuesta AJAX
        $formattedVeterinarians = $veterinarians->map(function ($vet) {
            return [
                'id' => $vet->id,
                'name' => $vet->user->name ?? 'Veterinario Desconocido', // Asegúrate de que el usuario exista
                'specialties' => $vet->specialties->pluck('name')->implode(', ')
            ];
        });

        Log::info('Veterinarios encontrados para el servicio:', ['veterinarians' => $formattedVeterinarians->toArray()]);
        return response()->json($formattedVeterinarians);
    }
    /**
     * Almacena una nueva cita en la base de datos.
     * Si hay una ServiceOrder pagada preseleccionada en la sesión, la vincula.
     * De lo contrario, crea una nueva ServiceOrder y redirige a la pasarela de pago.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::info('--- INICIO Método store para agendar cita ---');
        Log::info('Datos de la solicitud recibidos:', $request->all());

        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            Log::warning('No se encontró el perfil de cliente asociado para el usuario autenticado.', ['user_id' => Auth::id()]);
            return redirect()->back()->with('error', 'No se encontró el perfil de cliente asociado. Por favor, intente de nuevo.');
        }

        // 1. Validación de los datos del formulario
        $validatedData = $request->validate([
            'mascota_id' => [
                'required',
                'exists:mascotas,id',
                Rule::exists('mascotas', 'id')->where(function ($query) use ($cliente) {
                    $query->where('cliente_id', $cliente->id);
                }),
            ],
            'veterinarian_id' => 'required|exists:veterinarians,id',
            'date' => [
                'required',
                'date_format:Y-m-d H:i', // Espera formato 'YYYY-MM-DD HH:MM' del campo 'time_slot'
                // 'after_or_equal:now', // Esta validación es redundante con la lógica de abajo y puede ser removida
            ],
            'reason' => 'nullable|string|max:255',
            'service_id' => 'required|exists:services,id',
        ], [
            // 'date.after_or_equal' => 'No puedes agendar citas en el pasado.', // Mensaje que ya no debería aparecer aquí
            'date.date_format' => 'El formato de la fecha y hora no es válido. Asegúrate de seleccionar un horario válido.',
        ]);

        $selectedService = Service::findOrFail($validatedData['service_id']);
        $veterinarian = Veterinarian::findOrFail($validatedData['veterinarian_id']);

        // Calcular la hora de fin de la cita
        $startDateTime = Carbon::parse($validatedData['date']);
        $endDateTime = $startDateTime->copy()->addMinutes($selectedService->duration_minutes);

        try {
            DB::beginTransaction(); // Inicia una transacción para asegurar la atomicidad

            // --- INICIO DE LA LÓGICA DE SEGUNDA VERIFICACIÓN DE DISPONIBILIDAD (CRÍTICO) ---
            // Mapeo de nombres de días de la semana de inglés a español (con tildes)
            $dayOfWeekMapping = [
                'monday' => 'lunes',
                'tuesday' => 'martes',
                'wednesday' => 'miércoles',
                'thursday' => 'jueves',
                'friday' => 'viernes',
                'saturday' => 'sábado',
                'sunday' => 'domingo',
            ];
            $dayOfWeekEnglish = strtolower($startDateTime->format('l'));
            $dayOfWeek = $dayOfWeekMapping[$dayOfWeekEnglish] ?? null;

            if (is_null($dayOfWeek)) {
                Log::error('Error de lógica: Día de la semana no mapeado al intentar agendar cita.', [
                    'english_day' => $dayOfWeekEnglish,
                    'requested_date' => $validatedData['date']
                ]);
                throw new \Exception('No se pudo determinar el día de la semana para la cita.');
            }

            // ¡CAMBIO CLAVE AQUÍ! Obtener TODOS los horarios para ese día y veterinario
            $schedules = VeterinarianSchedule::where('veterinarian_id', $veterinarian->id)
                ->where('day_of_week', $dayOfWeek)
                ->orderBy('start_time') // Asegurarse de tenerlos ordenados
                ->get();

            if ($schedules->isEmpty()) {
                Log::warning('Intento de agendar cita fuera del horario de trabajo del veterinario (no se encontró schedule para el día).', [
                    'veterinarian_id' => $veterinarian->id,
                    'day_of_week' => $dayOfWeek
                ]);
                throw new \Exception('El veterinario no tiene un horario definido para ese día. Por favor, seleccione otro día o veterinario.');
            }

            $isWithinAnySchedule = false; // Bandera para saber si la cita cae en AL MENOS UN horario
            foreach ($schedules as $schedule) {
                $scheduleStartTime = Carbon::parse($schedule->start_time);
                $scheduleEndTime = Carbon::parse($schedule->end_time);

                // Ajustar el inicio y fin del horario al día de la cita para comparación
                $dailyScheduleStart = $startDateTime->copy()->setTimeFrom($scheduleStartTime);
                $dailyScheduleEnd = $startDateTime->copy()->setTimeFrom($scheduleEndTime);

                // Verificar si el slot de la cita está completamente dentro de ESTE rango de horario
                // La cita debe empezar en o después del inicio del turno Y terminar en o antes del fin del turno
                if ($startDateTime->greaterThanOrEqualTo($dailyScheduleStart) && $endDateTime->lessThanOrEqualTo($dailyScheduleEnd)) {
                    $isWithinAnySchedule = true;
                    break; // Se encontró un horario válido, no es necesario verificar los demás
                }
            }

            if (!$isWithinAnySchedule) {
                Log::warning('Intento de agendar cita fuera de TODOS los horarios de trabajo del veterinario para el día.', [
                    'requested_start' => $startDateTime->toDateTimeString(),
                    'requested_end' => $endDateTime->toDateTimeString(),
                    'day_of_week' => $dayOfWeek,
                    'veterinarian_id' => $veterinarian->id,
                    'schedules_checked' => $schedules->map(fn($s) => ['start' => $s->start_time, 'end' => $s->end_time])->toArray()
                ]);
                // Este es el mensaje que ve el usuario, hazlo claro.
                throw new \Exception('La hora seleccionada está fuera del horario de trabajo del veterinario para ese día. Por favor, selecciona un horario válido.');
            }

            // Verificar que el slot no sea en el pasado
            // Esta validación es CRÍTICA y debe estar ANTES de la verificación de superposiciones si no se hizo en el front.
            // La validación `after_or_equal:now` en `$request->validate` es útil pero esta es una doble seguridad más precisa.
            if ($startDateTime->lessThan(Carbon::now())) {
                Log::warning('Intento de agendar cita en el pasado (verificación final en backend).', [
                    'requested_start' => $startDateTime->toDateTimeString(),
                    'current_time' => Carbon::now()->toDateTimeString()
                ]);
                throw new \Exception('No puedes agendar citas en el pasado. Por favor, selecciona un horario futuro.');
            }

            // Verificar superposiciones con otras citas existentes para ese veterinario en esa fecha
            $existingOverlapAppointments = Appointment::where('veterinarian_id', $veterinarian->id)
                ->whereDate('date', $startDateTime->toDateString())
                ->where(function ($query) use ($startDateTime, $endDateTime) {
                    // Citas que comienzan durante el slot propuesto O citas que terminan durante el slot propuesto
                    // O citas que envuelven completamente el slot propuesto
                    $query->where(function ($q) use ($startDateTime, $endDateTime) {
                        $q->where('date', '<', $endDateTime) // La cita existente empieza antes de que termine nuestro slot
                            ->where('end_datetime', '>', $startDateTime); // Y termina después de que empiece nuestro slot
                    });
                })
                ->whereIn('status', ['pending', 'confirmed', 'completed']) // Considerar también 'completed' si ocupa el tiempo
                ->count();

            if ($existingOverlapAppointments > 0) {
                Log::warning('Intento de agendar cita en un slot ya ocupado.', [
                    'veterinarian_id' => $veterinarian->id,
                    'date' => $startDateTime->toDateTimeString(),
                    'overlaps_found' => $existingOverlapAppointments
                ]);
                throw new \Exception('El horario seleccionado ya no está disponible o se ha ocupado. Por favor, selecciona otro.');
            }
            // --- FIN DE LA LÓGICA DE SEGUNDA VERIFICACIÓN DE DISPONIBILIDAD ---


            // Lógica de vinculación a ServiceOrder existente o creación de una nueva
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
                        // No lanzamos excepción aquí, simplemente no vinculamos la orden y se creará una nueva si no hay otra forma de pago
                    }
                } else {
                    Log::warning('ServiceOrder preseleccionada inválida o impagada/desajuste de servicio. Ignorando preselección.', ['order_id' => $pendingServiceOrderIdToLink]);
                }
            }

            if ($linkedServiceOrder) {
                // Flujo 1: Cita agendada con un servicio PREVIAMENTE COMPRADO (ya pagado)
                $appointment = Appointment::create([
                    'user_id' => Auth::id(), // Asegúrate de guardar el user_id también
                    'mascota_id' => $validatedData['mascota_id'],
                    'veterinarian_id' => $validatedData['veterinarian_id'],
                    'service_id' => $validatedData['service_id'],
                    'date' => $startDateTime,
                    'end_datetime' => $endDateTime,
                    'reason' => $validatedData['reason'],
                    'status' => 'pending', // La cita comienza como pendiente (a realizar)
                    'service_order_id' => $linkedServiceOrder->id, // Vincula a la orden de servicio ya pagada
                    'notes' => 'Cita agendada con servicio pre-comprado.',
                ]);

                Session::forget('pending_service_order_id_to_link'); // Limpia la sesión
                DB::commit(); // Confirma la transacción
                Session::flash('success', '¡Cita agendada exitosamente con tu servicio adquirido! Revisa tus citas.');
                Log::info('Cita creada directamente y vinculada a ServiceOrder ' . $linkedServiceOrder->id . '.', ['appointment_id' => $appointment->id]);
                return redirect()->route('client.citas.show', $appointment->id); // Redirige a los detalles de la cita
            } else {
                // Flujo 2: Agendar cita normalmente (requiere pago)
                // Se crea una ServiceOrder PENDIENTE y se redirige al pago
                $newServiceOrder = ServiceOrder::create([
                    'user_id' => Auth::id(),
                    'service_id' => $selectedService->id,
                    'status' => 'PENDING',
                    'amount' => $selectedService->price,
                    'currency' => config('app.locale_currency', 'PEN'),
                ]);

                // Guardamos los datos de la cita en la sesión para crearla después del pago exitoso
                Session::put('current_service_order_id_for_payment', $newServiceOrder->id);
                Session::put('pending_appointment_data', [
                    'user_id' => Auth::id(),
                    'mascota_id' => $validatedData['mascota_id'],
                    'veterinarian_id' => $validatedData['veterinarian_id'],
                    'service_id' => $validatedData['service_id'],
                    'date' => $startDateTime->toDateTimeString(), // Almacena como string para la sesión
                    'end_datetime' => $endDateTime->toDateTimeString(), // Almacena como string
                    'reason' => $validatedData['reason'],
                    'status' => 'pending', // Estado inicial de la cita (se confirmará después del pago)
                    'service_order_id' => $newServiceOrder->id, // Para vincularlo después del pago
                    'notes' => 'Cita pendiente de pago.',
                ]);

                DB::commit(); // Confirma la creación de la ServiceOrder
                Log::info('Nueva ServiceOrder PENDIENTE creada para la cita, datos de la cita en la sesión.', [
                    'service_order_id' => $newServiceOrder->id,
                    'user_id' => Auth::id(),
                ]);

                return redirect()->route('payments.show_checkout_page', ['serviceOrderId' => $newServiceOrder->id])
                    ->with('info', 'Por favor, completa el pago para confirmar tu cita.');
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Deshace la transacción si algo salió mal
            Log::error('Error al agendar cita en CitaController@store: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validatedData ?? 'N/A'
            ]);
            // Mensaje de error más amigable para el usuario
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'Integrity constraint violation') !== false) {
                $errorMessage = 'Hubo un problema de integridad de datos. Es posible que el horario ya no esté disponible. Por favor, inténtelo de nuevo.';
            } else if (strpos($errorMessage, 'No se encontró el veterinario') !== false) {
                $errorMessage = 'El veterinario seleccionado no es válido.';
            } else if (strpos($errorMessage, 'El horario seleccionado ya no está disponible') !== false) {
                // Este mensaje ya es específico, lo mantenemos
            } else if (strpos($errorMessage, 'No puedes agendar citas en el pasado') !== false) {
                // Este mensaje ya es específico, lo mantenemos
            } else {
                $errorMessage = 'Ocurrió un error inesperado al agendar la cita. Por favor, inténtalo de nuevo más tarde.';
            }

            return redirect()->back()->withInput($request->all())->with('error', $errorMessage);
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
    public function getAvailableTimeSlots(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'veterinarian_id' => 'required|exists:veterinarians,id',
            'date' => 'required|date_format:Y-m-d',
            'service_id' => 'required|exists:services,id',
        ]);

        $veterinarianId = $request->input('veterinarian_id');
        $selectedDate = Carbon::parse($request->input('date'));
        $serviceId = $request->input('service_id');

        Log::info('--- INICIO getAvailableTimeSlots ---');
        Log::info('Parámetros recibidos:', [
            'veterinarian_id' => $veterinarianId,
            'date' => $selectedDate->toDateString(),
            'service_id' => $serviceId
        ]);

        $service = Service::find($serviceId);
        if (!$service || is_null($service->duration_minutes) || $service->duration_minutes <= 0) {
            Log::warning('Servicio no válido.', ['service_id' => $serviceId]);
            return response()->json(['slots' => []], 200);
        }
        $slotDurationMinutes = $service->duration_minutes;
        Log::info('Duración del servicio:', ['minutes' => $slotDurationMinutes]);

        $dayOfWeekMapping = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miércoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sábado',
            'sunday' => 'domingo',
        ];

        $dayOfWeekEnglish = strtolower($selectedDate->format('l'));
        $dayOfWeek = $dayOfWeekMapping[$dayOfWeekEnglish] ?? null;

        if (is_null($dayOfWeek)) {
            Log::error('Día no reconocido:', ['english_day' => $dayOfWeekEnglish]);
            return response()->json(['slots' => []], 500);
        }

        Log::info('Día mapeado:', ['day_of_week' => $dayOfWeek]);

        $schedules = VeterinarianSchedule::where('veterinarian_id', $veterinarianId)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            Log::warning('No hay horarios:', ['vet_id' => $veterinarianId, 'day' => $dayOfWeek]);
            return response()->json(['slots' => []], 200);
        }

        Log::info('Horarios encontrados:', $schedules->map(function ($s) {
            return ['start' => $s->start_time, 'end' => $s->end_time];
        })->toArray());

        $existingAppointments = Appointment::where('veterinarian_id', $veterinarianId)
            ->whereDate('date', $selectedDate->toDateString())
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->get();

        Log::info('Citas existentes:', $existingAppointments->map(function ($a) {
            return ['start' => $a->date->format('H:i'), 'end' => $a->end_datetime->format('H:i')];
        })->toArray());

        $allAvailableSlots = [];
        $now = Carbon::now();
        $isToday = $selectedDate->isToday();

        Log::debug('Hoy es hoy?', ['isToday' => $isToday, 'now' => $now->format('Y-m-d H:i:s')]);

        foreach ($schedules as $schedule) {
            $startTime = Carbon::parse($schedule->start_time);
            $endTime = Carbon::parse($schedule->end_time);

            $currentSlotStart = $selectedDate->copy()->setTimeFrom($startTime);
            $endOfScheduleDay = $selectedDate->copy()->setTimeFrom($endTime);

            while ($currentSlotStart->lessThan($endOfScheduleDay)) {
                $currentSlotEnd = $currentSlotStart->copy()->addMinutes($slotDurationMinutes);

                if ($currentSlotEnd->greaterThan($endOfScheduleDay)) {
                    break;
                }

                $isAvailable = true;

                if ($isToday && $currentSlotStart->lessThan($now)) {
                    $isAvailable = false;
                    Log::debug('Slot pasado:', [
                        'slot_start' => $currentSlotStart->format('Y-m-d H:i:s'),
                        'now' => $now->format('Y-m-d H:i:s')
                    ]);
                } else {
                    foreach ($existingAppointments as $app) {
                        if ($currentSlotStart->lt($app->end_datetime) && $currentSlotEnd->gt($app->date)) {
                            $isAvailable = false;
                            Log::debug('Solapado con cita:', [
                                'slot_start' => $currentSlotStart->format('H:i'),
                                'slot_end' => $currentSlotEnd->format('H:i'),
                                'cita_start' => $app->date->format('H:i'),
                                'cita_end' => $app->end_datetime->format('H:i'),
                            ]);
                            break;
                        }
                    }
                }

                if ($isAvailable) {
                    $allAvailableSlots[] = [
                        'start' => $currentSlotStart->format('H:i'),
                        'end' => $currentSlotEnd->format('H:i'),
                        'full_datetime' => $selectedDate->format('Y-m-d') . ' ' . $currentSlotStart->format('H:i'),
                    ];
                    Log::debug('✅ Slot DISPONIBLE:', ['start' => $currentSlotStart->format('H:i')]);
                } else {
                    Log::debug('❌ Slot NO DISPONIBLE:', ['start' => $currentSlotStart->format('H:i')]);
                }

                $currentSlotStart->addMinutes($slotDurationMinutes);
            }
        }

        Log::info('Slots finales:', $allAvailableSlots);
        Log::info('--- FIN getAvailableTimeSlots ---');

        return response()->json(['slots' => $allAvailableSlots]);
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
                // 'user_id' => Auth::id(), //añadi aqui oye fea || actualizado...
                'service_order_id' => $serviceOrder->id, // ¡VINCULA LA CITA A LA ORDEN DE SERVICIO PAGADA!
            ]));
            Log::info('Cita creada exitosamente y vinculada a ServiceOrder:', ['appointment_id' => $appointment->id, 'service_order_id' => $serviceOrder->id]);
            return redirect()->route('client.citas.index')->with('success', '¡Pago procesado y cita agendada exitosamente, te enviamos un email con los detalles!');
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


    //intento con chatgpt :(

    public function edit(Appointment $appointment)
    {
        // Asegúrate de que solo el dueño de la cita puede editarla
        // if ($appointment->user_id !== Auth::id()) {
        //     abort(403, 'No tienes permiso para reprogramar esta cita.');
        // }

        // Solo permitir reprogramar si está pendiente
        if ($appointment->status !== 'pending') {
            return redirect()->route('client.citas.index')->with('error', 'Solo puedes reprogramar citas pendientes.');
        }

        $mascotas = Auth::user()->cliente->mascotas;
        $service = $appointment->service;

        return view('client.citas.reprogramar', [
            'appointment' => $appointment,
            'mascotas' => $mascotas,
            'preselectedService' => $service,
        ]);
    }
    public function update(Request $request, Appointment $appointment)
    {
        // if ($appointment->user_id !== Auth::id()) {
        //     abort(403, 'No tienes permiso para modificar esta cita.');
        // }

        if ($appointment->status !== 'pending') {
            return redirect()->route('client.citas.index')->with('error', 'Solo puedes reprogramar citas pendientes.');
        }

        $request->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:500',
        ]);

        // Validar que no haya otra cita en ese horario
        $start = Carbon::parse($request->date);
        $end = $start->copy()->addMinutes($appointment->service->duration_minutes);

        $solapamiento = Appointment::where('veterinarian_id', $appointment->veterinarian_id)
            ->where('id', '!=', $appointment->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('date', '<', $start)
                            ->where('end_datetime', '>', $end);
                    });
            })
            ->exists();

        if ($solapamiento) {
            return redirect()->back()->withInput()->with('error', 'Ya existe una cita en ese horario.');
        }

        $appointment->update([
            'mascota_id' => $request->mascota_id,
            'date' => $start,
            'end_datetime' => $end,
            'reason' => $request->reason,
        ]);

        return redirect()->route('client.citas.show', $appointment->id)
            ->with('success', '¡Cita reprogramada exitosamente!');
    }


    //fin de intentoc con chatgtp



    /*
    |--------------------------------------------------------------------------
    | Métodos para la Reprogramación de Citas
    |--------------------------------------------------------------------------
    */

    public function showReprogrammingForm(Appointment $appointment): View|\Illuminate\Http\RedirectResponse
    {
        $client = Auth::user()->cliente;

        // Validar que la cita pertenezca al cliente autenticado
        if (!$client || $appointment->mascota->cliente_id !== $client->id) {
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para reprogramar esta cita.');
        }

        $appointment->load('mascota', 'veterinarian.user', 'service');

        // Buscar la solicitud de reprogramación ACTIVA para esta cita.
        // Una solicitud activa es la última que no ha sido aplicada, cancelada o marcada como obsoleta.
        // El estado 'pending_reprogramming' en la cita original indica que hay una negociación en curso.
        $activeReprogrammingRequest = ReprogrammingRequest::where('appointment_id', $appointment->id)
            ->whereNotIn('status', ['accepted_by_both', 'applied', 'cancelled_by_request', 'obsolete_by_new_proposal'])
            ->orderBy('created_at', 'desc') // Obtener la más reciente entre las "no finalizadas"
            ->first();

        if ($activeReprogrammingRequest) {
            Log::info('Redirigiendo a estado de reprogramación existente para cliente.', [
                'appointment_id' => $appointment->id,
                'request_id' => $activeReprogrammingRequest->id,
                'status' => $activeReprogrammingRequest->status
            ]);
            return redirect()->route('client.citas.reprogram.status', $appointment->id)
                ->with('info', 'Ya existe una solicitud de reprogramación en curso para esta cita. Por favor, gestiónala desde allí.');
        }

        // Si la cita ya está reprogramada o cancelada, no se debe permitir iniciar una nueva solicitud.
        if ($appointment->status === 'reprogrammed' || $appointment->status === 'cancelled') {
            return redirect()->route('client.citas.index')->with('info', 'Esta cita ya ha sido gestionada y no puede ser reprogramada.');
        }

        Log::info('Mostrando formulario de reprogramación inicial (sin solicitud activa) para cita.', ['appointment_id' => $appointment->id]);
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

        // 1. Validar campos básicos del formulario (sin Validator::make())
        // CAMBIO: Usar 'proposed_start_date_time' en lugar de 'new_appointment_date'
        if (empty($request->input('proposed_start_date_time'))) {
            return redirect()->back()->withInput()->with('error', 'La nueva fecha y hora de la cita es obligatoria.');
        }
        if (empty($request->input('reprogramming_reason'))) {
            return redirect()->back()->withInput()->with('error', 'El motivo de la reprogramación es obligatorio.');
        }
        if (strlen($request->input('reprogramming_reason')) > 500) {
            return redirect()->back()->withInput()->with('error', 'El motivo de la reprogramación no debe exceder los 500 caracteres.');
        }

        // CAMBIO: Usar 'proposed_start_date_time'
        $newDateTime = Carbon::parse($request->input('proposed_start_date_time'));

        // Asegurarse de que la nueva fecha sea en el futuro
        if ($newDateTime->lessThan(Carbon::now())) {
            return redirect()->back()->withInput()->with('error', 'La nueva fecha y hora no puede ser en el pasado.');
        }

        // Cargar el servicio para obtener su duración
        $appointment->loadMissing('service');
        $veterinarianId = $appointment->veterinarian_id;
        $serviceId = $appointment->service_id;
        $appointmentId = $appointment->id; // ID de la cita actual para ignorarla en solapamientos

        // Obtener la duración del servicio
        $service = Service::find($serviceId);
        if (!$service || empty($service->duration_minutes) || $service->duration_minutes <= 0) {
            return redirect()->back()->withInput()->with('error', 'La duración del servicio para la validación no es válida. Contacte al administrador.');
        }
        $slotDurationMinutes = $service->duration_minutes;
        $newEndDateTime = $newDateTime->copy()->addMinutes($slotDurationMinutes);

        // 2. Verificar horarios de trabajo del veterinario para el nuevo día
        $dayOfWeekEnglish = strtolower($newDateTime->format('l'));
        $dayOfWeekMapping = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miércoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sábado',
            'sunday' => 'domingo',
        ];
        $dayOfWeek = $dayOfWeekMapping[$dayOfWeekEnglish] ?? null;

        if (!$dayOfWeek) {
            return redirect()->back()->withInput()->with('error', 'Día de la semana no reconocido para el horario propuesto.');
        }

        $schedules = VeterinarianSchedule::where('veterinarian_id', $veterinarianId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        $isInSchedule = false;
        foreach ($schedules as $schedule) {
            $scheduleStart = Carbon::parse($newDateTime->format('Y-m-d') . ' ' . $schedule->start_time);
            $scheduleEnd = Carbon::parse($newDateTime->format('Y-m-d') . ' ' . $schedule->end_time);

            if ($newDateTime->gte($scheduleStart) && $newEndDateTime->lte($scheduleEnd)) {
                $isInSchedule = true;
                break;
            }
        }

        if (!$isInSchedule) {
            return redirect()->back()->withInput()->with('error', 'La nueva hora seleccionada no está dentro del horario de trabajo del veterinario para ese día.');
        }

        // 3. Verificar solapamiento con otras citas existentes
        $overlappingAppointment = Appointment::where('veterinarian_id', $veterinarianId)
            ->where('id', '!=', $appointmentId) // Ignorar la cita que se está reprogramando
            ->whereIn('status', ['pending', 'confirmed', 'completed', 'pending_reprogramming', 'reprogrammed'])
            ->where(function ($query) use ($newDateTime, $newEndDateTime) {
                $query->where(function ($q) use ($newDateTime, $newEndDateTime) {
                    $q->where('date', '<', $newEndDateTime)
                        ->where('end_datetime', '>', $newDateTime);
                });
            })
            ->first();

        if ($overlappingAppointment) {
            return redirect()->back()->withInput()->with('error', 'La nueva fecha y hora se superpone con otra cita existente para este veterinario.');
        }
        // --- FIN DE TODAS LAS VALIDACIONES MANUALES ---

        try {
            // Marcar cualquier solicitud de reprogramación NO FINALIZADA como obsoleta para esta cita.
            ReprogrammingRequest::where('appointment_id', $appointment->id)
                ->whereNotIn('status', ['accepted_by_both', 'applied', 'cancelled_by_request', 'obsolete_by_new_proposal'])
                ->update(['status' => 'obsolete_by_new_proposal']);

            // Crear la nueva solicitud de reprogramación (iniciada por el cliente)
            $reprogrammingRequest = ReprogrammingRequest::create([
                'appointment_id' => $appointment->id,
                'client_id' => $client->id,
                'veterinarian_id' => $veterinarianId,
                'requester_type' => 'client', // El cliente es quien inicia la solicitud
                'proposed_start_date_time' => $newDateTime,
                'proposed_end_date_time' => $newEndDateTime,
                'reprogramming_reason' => $request->reprogramming_reason,
                'client_confirmed' => true, // El cliente confirma su propia propuesta al enviarla
                'veterinarian_confirmed' => false, // Todavía no ha confirmado el veterinario
                'status' => 'pending_veterinarian_confirmation', // Esperando confirmación del veterinario
            ]);

            // Actualizar el estado de la cita original a 'pending_reprogramming'
            if ($appointment->status !== 'pending_reprogramming') {
                $appointment->status = 'pending_reprogramming';
                $appointment->save();
            }

            Log::info('Solicitud de reprogramación inicial creada por cliente.', ['request_id' => $reprogrammingRequest->id, 'appointment_id' => $appointment->id]);

            return redirect()->route('client.citas.reprogram.status', $appointment->id)->with('success', 'Tu solicitud de reprogramación ha sido enviada con éxito. El veterinario la revisará pronto.');
        } catch (\Exception $e) {
            Log::error('Error al procesar solicitud de reprogramación inicial por cliente: ' . $e->getMessage(), [
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

        // Obtener la ÚLTIMA solicitud de reprogramación que NO ha sido FINALIZADA
        $reprogrammingRequest = ReprogrammingRequest::where('appointment_id', $appointment->id)
            ->whereNotIn('status', ['accepted_by_both', 'applied', 'cancelled_by_request', 'obsolete_by_new_proposal'])
            ->orderBy('created_at', 'desc')
            ->with(['appointment.mascota', 'appointment.veterinarian.user'])
            ->first();

        // Si no hay una solicitud activa pendiente o la cita ya está en un estado final
        if (!$reprogrammingRequest || $appointment->status === 'reprogrammed' || $appointment->status === 'cancelled') {
            if ($appointment->status === 'reprogrammed') {
                return redirect()->route('client.citas.index')->with('success', 'Tu cita ya ha sido reprogramada exitosamente.');
            }
            if ($appointment->status === 'cancelled') {
                return redirect()->route('client.citas.index')->with('info', 'Tu cita ha sido cancelada.');
            }
            // Si la cita está agendada pero no hay solicitudes activas, redirigir al formulario para iniciar.
            Log::info('No se encontró una solicitud de reprogramación activa para la cita. Redirigiendo a formulario inicial.', ['appointment_id' => $appointment->id]);
            return redirect()->route('client.citas.reprogram.form', $appointment->id)
                ->with('info', 'No hay una solicitud de reprogramación activa para esta cita. Puedes iniciar una nueva si lo deseas.');
        }

        Log::info('Mostrando estado de reprogramación para cita y solicitud.', ['appointment_id' => $appointment->id, 'request_id' => $reprogrammingRequest->id, 'status' => $reprogrammingRequest->status]);
        return view('client.citas.reprogram_status', compact('reprogrammingRequest', 'appointment'));
    }
    /**
     * Permite al cliente responder a una propuesta de reprogramación.
     * Puede ACEPTAR o CONTRA-PROPONER.
     * NO hay un rechazo simple que no involucre una contrapropuesta o cancelación.
     */
    public function respondToReprogrammingRequest(Request $request, Appointment $appointment): \Illuminate\Http\RedirectResponse
    {
        $client = Auth::user()->cliente;

        if (!$client || $appointment->mascota->cliente_id !== $client->id) {
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para interactuar con esta cita.');
        }

        // Obtener la solicitud activa actual que el cliente debe responder
        $currentRequest = ReprogrammingRequest::where('appointment_id', $appointment->id)
            ->where('status', 'pending_client_confirmation') // Esperando respuesta del cliente
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$currentRequest) {
            return redirect()->route('client.citas.reprogram.status', $appointment->id)->with('error', 'No hay una propuesta de reprogramación activa que requiera tu respuesta.');
        }

        $action = $request->input('action'); // Puede ser 'accept' o 'counter_propose'

        try {
            if ($action === 'accept') {
                if ($currentRequest->status !== 'pending_client_confirmation' || $currentRequest->requester_type !== 'veterinarian') {
                    return redirect()->back()->with('error', 'No puedes aceptar esta propuesta en este momento.');
                }

                $currentRequest->client_confirmed = true;
                $currentRequest->save();

                if ($currentRequest->veterinarian_confirmed === true && $currentRequest->client_confirmed === true) {
                    $appointment->update([
                        'date' => $currentRequest->proposed_start_date_time,
                        'end_datetime' => $currentRequest->proposed_end_date_time,
                        'status' => 'reprogrammed',
                    ]);
                    $currentRequest->update(['status' => 'applied']);
                    Log::info('Cita reprogramada automáticamente tras aceptación del cliente.', ['request_id' => $currentRequest->id, 'appointment_id' => $appointment->id]);
                    return redirect()->route('client.citas.reprogram.status', $appointment->id)->with('success', '¡Has aceptado la nueva fecha! La cita ha sido reprogramada con éxito.');
                }
                Log::info('Cliente aceptó propuesta del veterinario. Esperando que el sistema actualice.', ['request_id' => $currentRequest->id, 'appointment_id' => $appointment->id]);
                return redirect()->route('client.citas.reprogram.status', $appointment->id)->with('info', 'Has aceptado la propuesta. Esperando la confirmación final.');
            } elseif ($action === 'counter_propose') {
                // El cliente rechaza la propuesta actual y envía una nueva
                $veterinarianId = $appointment->veterinarian_id;
                $serviceId = $appointment->service_id;
                $appointmentId = $appointment->id; // ID de la cita actual para ignorarla en solapamientos

                // Validar campos básicos de la contrapropuesta
                if (empty($request->input('proposed_start_date_time'))) {
                    return redirect()->back()->withInput()->with('error', 'La nueva fecha y hora para la contrapropuesta es obligatoria.')->fragment('counterProposeModal'); // Vuelve al modal
                }
                if (empty($request->input('reprogramming_reason'))) {
                    return redirect()->back()->withInput()->with('error', 'El motivo de la contrapropuesta es obligatorio.')->fragment('counterProposeModal');
                }
                if (strlen($request->input('reprogramming_reason')) > 500) {
                    return redirect()->back()->withInput()->with('error', 'El motivo de la contrapropuesta no debe exceder los 500 caracteres.')->fragment('counterProposeModal');
                }


                $newProposedStart = Carbon::parse($request->input('proposed_start_date_time'));

                // Asegurarse de que la nueva fecha propuesta sea en el futuro estricto
                if ($newProposedStart->lessThanOrEqualTo(Carbon::now())) {
                    return redirect()->back()->withInput()->with('error', 'La nueva fecha y hora de la contrapropuesta debe ser en el futuro.')->fragment('counterProposeModal');
                }

                // 1. Obtener la duración del servicio
                $appointment->loadMissing('service'); // Asegurarse de que el servicio está cargado
                $service = $appointment->service;
                if (!$service || empty($service->duration_minutes) || $service->duration_minutes <= 0) {
                    return redirect()->back()->withInput()->with('error', 'La duración del servicio para la validación no es válida. Contacte al administrador.')->fragment('counterProposeModal');
                }
                $slotDurationMinutes = $service->duration_minutes;
                $newProposedEnd = $newProposedStart->copy()->addMinutes($slotDurationMinutes);

                // 2. Verificar horarios de trabajo del veterinario para el nuevo día
                $dayOfWeekEnglish = strtolower($newProposedStart->format('l'));
                $dayOfWeekMapping = [
                    'monday' => 'lunes',
                    'tuesday' => 'martes',
                    'wednesday' => 'miércoles',
                    'thursday' => 'jueves',
                    'friday' => 'viernes',
                    'saturday' => 'sábado',
                    'sunday' => 'domingo',
                ];
                $dayOfWeek = $dayOfWeekMapping[$dayOfWeekEnglish] ?? null;

                if (!$dayOfWeek) {
                    return redirect()->back()->withInput()->with('error', 'Día de la semana no reconocido para el horario propuesto.')->fragment('counterProposeModal');
                }

                $schedules = VeterinarianSchedule::where('veterinarian_id', $veterinarianId)
                    ->where('day_of_week', $dayOfWeek)
                    ->get();

                $isInSchedule = false;
                foreach ($schedules as $schedule) {
                    $scheduleStart = Carbon::parse($newProposedStart->format('Y-m-d') . ' ' . $schedule->start_time);
                    $scheduleEnd = Carbon::parse($newProposedStart->format('Y-m-d') . ' ' . $schedule->end_time);

                    if ($newProposedStart->gte($scheduleStart) && $newProposedEnd->lte($scheduleEnd)) {
                        $isInSchedule = true;
                        break;
                    }
                }

                if (!$isInSchedule) {
                    return redirect()->back()->withInput()->with('error', 'La nueva hora seleccionada no está dentro del horario de trabajo del veterinario para ese día.')->fragment('counterProposeModal');
                }

                // 3. Verificar solapamiento con otras citas existentes
                $overlappingAppointment = Appointment::where('veterinarian_id', $veterinarianId)
                    ->where('id', '!=', $appointmentId)
                    ->whereIn('status', ['pending', 'confirmed', 'completed', 'pending_reprogramming', 'reprogrammed'])
                    ->where(function ($query) use ($newProposedStart, $newProposedEnd) {
                        $query->where(function ($q) use ($newProposedStart, $newProposedEnd) {
                            $q->where('date', '<', $newProposedEnd)
                                ->where('end_datetime', '>', $newProposedStart);
                        });
                    })
                    ->first();

                if ($overlappingAppointment) {
                    return redirect()->back()->withInput()->with('error', 'La nueva fecha y hora se superpone con otra cita existente para este veterinario.')->fragment('counterProposeModal');
                }
                // --- FIN DE TODAS LAS VALIDACIONES MANUALES PARA CONTRAPROPUESTA ---

                // Marcar la solicitud actual como obsoleta
                $currentRequest->update(['status' => 'obsolete_by_new_proposal']);

                // Crear una NUEVA ReprogrammingRequest con la contrapropuesta del cliente
                $newReprogrammingRequest = ReprogrammingRequest::create([
                    'appointment_id' => $appointment->id,
                    'client_id' => $client->id,
                    'veterinarian_id' => $veterinarianId,
                    'requester_type' => 'client',
                    'proposed_start_date_time' => $newProposedStart,
                    'proposed_end_date_time' => $newProposedEnd,
                    'reprogramming_reason' => $request->reprogramming_reason,
                    'client_confirmed' => true, // El cliente ya confirma su propia contrapropuesta
                    'veterinarian_confirmed' => false, // Pendiente de la confirmación del veterinario
                    'status' => 'pending_veterinarian_confirmation',
                ]);

                Log::info('Cliente envió contrapropuesta de reprogramación.', ['old_request_id' => $currentRequest->id, 'new_request_id' => $newReprogrammingRequest->id, 'appointment_id' => $appointment->id]);
                return redirect()->route('client.citas.reprogram.status', $appointment->id)->with('success', 'Tu contrapropuesta ha sido enviada al veterinario.');
            } else {
                return redirect()->back()->with('error', 'Acción inválida.');
            }
        } catch (\Exception $e) {
            Log::error('Error al responder a solicitud de reprogramación por cliente: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'appointment_id' => $appointment->id,
                'action' => $action
            ]);
            return redirect()->back()->withInput()->with('error', 'Hubo un error al procesar tu respuesta: ' . $e->getMessage());
        }
    }

    /**
     * Permite al cliente retirar una propuesta de reprogramación que él mismo inició
     * y que está pendiente de confirmación del veterinario.
     */
    public function retractClientProposal(ReprogrammingRequest $reprogrammingRequest): \Illuminate\Http\RedirectResponse
    {
        $client = Auth::user()->cliente;

        // Validar que la solicitud pertenezca al cliente, que él sea el "requester"
        // y que esté pendiente de la confirmación del veterinario.
        if (!$client || $reprogrammingRequest->client_id !== $client->id || $reprogrammingRequest->requester_type !== 'client') {
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para retirar esta propuesta.');
        }

        if ($reprogrammingRequest->status !== 'pending_veterinarian_confirmation') {
            return redirect()->route('client.citas.reprogram.status', $reprogrammingRequest->appointment_id)->with('info', 'Esta propuesta ya no está pendiente de tu retiro.');
        }

        try {
            $reprogrammingRequest->update([
                'status' => 'cancelled_by_request', // La solicitud de reprogramación fue anulada por el cliente.
                'client_confirmed' => false,
            ]);

            // Restaurar la cita principal a su estado 'confirmed' si no hay otra razón para que esté en 'pending_reprogramming'.
            $reprogrammingRequest->appointment->update(['status' => 'confirmed']);

            Log::info('Cliente retiró su propia propuesta de reprogramación.', ['request_id' => $reprogrammingRequest->id, 'appointment_id' => $reprogrammingRequest->appointment_id]);
            return redirect()->route('client.citas.reprogram.status', $reprogrammingRequest->appointment_id)->with('success', 'Tu propuesta de reprogramación ha sido retirada. La cita original ha sido restaurada a su estado confirmado.');
        } catch (\Exception $e) {
            Log::error('Error al retirar propuesta de reprogramación por cliente: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_id' => $reprogrammingRequest->id,
            ]);
            return redirect()->back()->with('error', 'Hubo un error al retirar tu propuesta: ' . $e->getMessage());
        }
    }


    public function cancelAppointment(Request $request, Appointment $appointment): \Illuminate\Http\RedirectResponse
    {
        $client = Auth::user()->cliente;
        if (!$client || $appointment->mascota->cliente_id !== $client->id) {
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para cancelar esta cita.');
        }

        // (Opcional: puedes añadir una confirmación JavaScript o un campo oculto si el usuario ya vio la advertencia)
        // if (!$request->input('confirmed_cancel')) {
        //     return redirect()->back()->with('confirm_cancel_message', 'Al cancelar esta cita, perderás el dinero pagado y la cita será eliminada. ¿Estás seguro?');
        // }

        try {
            // 1. Marcar la cita como cancelada
            $appointment->status = 'cancelled';
            $appointment->save();

            // 2. Marcar cualquier solicitud de reprogramación activa/pendiente para esta cita como cancelada
            ReprogrammingRequest::where('appointment_id', $appointment->id)
                ->whereNotIn('status', ['accepted_by_both', 'applied', 'cancelled_by_request', 'obsolete_by_new_proposal'])
                ->update(['status' => 'cancelled_by_request']); // 'cancelled_by_request' abarca la cancelación de la negociación

            Log::info('Cita cancelada definitivamente por el cliente.', ['appointment_id' => $appointment->id, 'client_id' => $client->id]);
            return redirect()->route('client.citas.index')->with('success', 'Tu cita ha sido cancelada con éxito. Por favor, ten en cuenta nuestras políticas de cancelación.');
        } catch (\Exception $e) {
            Log::error('Error al cancelar cita por el cliente: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'appointment_id' => $appointment->id
            ]);
            return redirect()->back()->with('error', 'Hubo un error al cancelar tu cita: ' . $e->getMessage());
        }
    }


    public function citasAgendadas(Request $request)
    {
        $veterinario = Auth::user()->veterinarian;

        // ... (resto de tu código de citasAgendadas)
        // No se cambia, asumiendo que es para el rol de veterinario y esta función se llamará desde otra ruta
        // o que tu sistema de permisos lo maneja.

        // Simplemente copio el cuerpo para que tu archivo no se modifique
        $status = $request->input('status');
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
            ->when(!$status, fn($q) => $q->whereIn('status', ['pending', 'confirmed', 'reprogrammed']))

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

    ///metoodo que incluyo para usarlo en el create form de citas, par mostrar que dias trabaja el veterinario 
    //escogido
    public function getVeterinarianWorkingDays(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'veterinarian_id' => 'required|exists:veterinarians,id',
        ]);

        $veterinarianId = $request->input('veterinarian_id');

        // Obtener los horarios del veterinario para ver qué días atiende
        $schedules = VeterinarianSchedule::where('veterinarian_id', $veterinarianId)
            ->orderByRaw("FIELD(day_of_week, 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo')")
            ->pluck('day_of_week') // Obtiene solo los nombres de los días
            ->toArray(); // Convierte la colección a un array

        if (empty($schedules)) {
            return response()->json(['message' => 'Este veterinario no tiene horarios definidos.', 'workingDays' => []], 200);
        }

        return response()->json(['workingDays' => $schedules], 200);
    }

    ## Lógica de Reprogramación de Citas para el Cliente

    /**
     * Muestra el detalle de una solicitud de reprogramación para que el cliente la revise.
     * Esta es la página a la que el cliente es redirigido desde la notificación.
     */
    public function showReprogrammingRequest(ReprogrammingRequest $reprogrammingRequest): View|\Illuminate\Http\RedirectResponse
    {
        // Cargamos la relación `client.user` explícitamente para asegurar que está disponible
        // y poder verificar que el usuario autenticado sea el dueño de la solicitud.
        $reprogrammingRequest->load('client.user');

        // Verificamos que el usuario autenticado sea el propietario de la solicitud.
        // Si tu modelo Cliente no tiene un campo user_id, necesitarás adaptar esto
        // para la forma en que se relaciona Cliente con User.
        if (Auth::id() !== ($reprogrammingRequest->client->user->id ?? null)) {
            Log::warning('Intento de acceso no autorizado a solicitud de reprogramación.', [
                'user_id' => Auth::id(),
                'attempted_reprogramming_request_id' => $reprogrammingRequest->id,
                'request_client_user_id' => $reprogrammingRequest->client->user->id ?? 'N/A'
            ]);
            return redirect()->route('client.citas.index')->with('error', 'No tienes permiso para ver esta solicitud de reprogramación.');
        }

        // Si la solicitud ya fue respondida (aceptada o rechazada), redirigimos o mostramos un mensaje.
        if ($reprogrammingRequest->status !== 'pending') {
            return redirect()->route('client.citas.index')->with('info', 'Esta solicitud ya ha sido procesada.');
        }

        return view('client.reprogramming_requests.show', compact('reprogrammingRequest'));
    }

    /**
     * Procesa la aceptación de una solicitud de reprogramación por parte del cliente.
     */
    public function acceptReprogrammingRequest(Request $request, ReprogrammingRequest $reprogrammingRequest): \Illuminate\Http\RedirectResponse
    {
        // Verificamos que el cliente autenticado sea el dueño de la solicitud.
        if (Auth::id() !== ($reprogrammingRequest->client->user->id ?? null)) {
            return back()->with('error', 'No tienes permiso para realizar esta acción.');
        }

        // Verificamos si ya ha sido procesada.
        if ($reprogrammingRequest->status !== 'pending') {
            return back()->with('info', 'Esta solicitud ya ha sido procesada.');
        }

        try {
            DB::beginTransaction(); // Iniciamos una transacción para asegurar la atomicidad.

            // 1. Actualizamos la tabla 'reprogramming_requests'.
            $reprogrammingRequest->update([
                'client_confirmed' => true,
                'client_confirmed_at' => now(),
                'status' => 'accepted', // Estado final de la solicitud de reprogramación.
            ]);

            // 2. Actualizamos la tabla 'appointments' (la cita original).
            $appointment = $reprogrammingRequest->appointment;
            $appointment->update([
                'date' => $reprogrammingRequest->proposed_start_date_time->format('Y-m-d'), // Solo la fecha
                'time' => $reprogrammingRequest->proposed_start_date_time->format('H:i:s'), // La hora de inicio de la propuesta
                'end_datetime' => $reprogrammingRequest->proposed_end_date_time, // La hora final de la propuesta
                'status' => 'reprogramada', // Cambiamos el estado de la cita original.
            ]);

            // 3. Notificamos al veterinario que la solicitud ha sido aceptada.
            $veterinarianUser = $reprogrammingRequest->veterinarian->user;
            if ($veterinarianUser) {
                $veterinarianUser->notify(new ReprogrammingRequestStatusUpdate($reprogrammingRequest, 'aceptada'));
            }

            DB::commit(); // Confirmamos la transacción.
            return redirect()->route('client.citas.index')->with('success', '¡Cita reprogramada con éxito! Revisa los detalles en tus citas.');
        } catch (\Exception $e) {
            DB::rollBack(); // Deshacemos la transacción si algo sale mal.
            Log::error('Error al aceptar reprogramación de cita en CitaController@acceptReprogrammingRequest: ' . $e->getMessage(), [
                'request_id' => $reprogrammingRequest->id,
                'user_id' => Auth::id(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Ocurrió un error al procesar tu aceptación. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Procesa el rechazo de una solicitud de reprogramación por parte del cliente.
     */
    public function rejectReprogrammingRequest(Request $request, ReprogrammingRequest $reprogrammingRequest): \Illuminate\Http\RedirectResponse
    {
        // Verificamos que el cliente autenticado sea el dueño de la solicitud.
        if (Auth::id() !== ($reprogrammingRequest->client->user->id ?? null)) {
            return back()->with('error', 'No tienes permiso para realizar esta acción.');
        }

        // Verificamos si ya ha sido procesada.
        if ($reprogrammingRequest->status !== 'pending') {
            return back()->with('info', 'Esta solicitud ya ha sido procesada.');
        }

        try {
            DB::beginTransaction(); // Iniciamos una transacción.

            // 1. Actualizamos la tabla 'reprogramming_requests'.
            $reprogrammingRequest->update([
                'client_confirmed' => false,
                'status' => 'rejected',
            ]);

            // La cita original NO se actualiza, queda como estaba antes de la solicitud de reprogramación.
            // Si tenías un estado intermedio en Appointment para indicar que estaba "en proceso de reprogramación",
            // podrías revertirlo aquí si fuera necesario.

            // 2. Notificamos al veterinario que la solicitud ha sido rechazada.
            $veterinarianUser = $reprogrammingRequest->veterinarian->user;
            if ($veterinarianUser) {
                $veterinarianUser->notify(new ReprogrammingRequestStatusUpdate($reprogrammingRequest, 'rechazada'));
            }

            DB::commit(); // Confirmamos la transacción.
            return redirect()->route('client.citas.index')->with('info', 'Solicitud de reprogramación rechazada. La cita original se mantiene.');
        } catch (\Exception $e) {
            DB::rollBack(); // Deshacemos la transacción.
            Log::error('Error al rechazar reprogramación de cita en CitaController@rejectReprogrammingRequest: ' . $e->getMessage(), [
                'request_id' => $reprogrammingRequest->id,
                'user_id' => Auth::id(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Ocurrió un error al procesar tu rechazo. Por favor, inténtalo de nuevo.');
        }
    }
}

//resources/views/client/citas/create.blade (blade para crear cita)

//resources/views/client/citas/reprogram_form.blade (blade para el formulario de reprogramacion)

//resources/views/client/citas/reprogma_status.blade 
//resources/views/client/citas/index.blade (aqui se muestran las citas que ya tiene el boton para reprogamar)
