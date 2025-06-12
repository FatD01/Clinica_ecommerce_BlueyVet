<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Mascota;
use App\Models\Service; // ¡IMPORTANTE: Asegúrate de que este use esté aquí!
use App\Models\ServiceOrder; // ¡IMPORTANTE: Asegúrate de que este use esté aquí!
use App\Models\Veterinarian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon; // ¡IMPORTANTE: Asegúrate de que este use esté aquí si usas Carbon::parse!
use Illuminate\View\View; // ¡IMPORTANTE: Asegúrate de que este use esté aquí!

class CitaController extends Controller
{
    /**
     * Define los middlewares para este controlador.
     */
    protected function middleware(): array
    {
        return [
            'auth', // Todas las acciones de este controlador requieren autenticación
        ];
    }

   
    public function index(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $cliente = Auth::user()->cliente;

        // Si no se encuentra un perfil de cliente, redirige al dashboard con un error.
        if (!$cliente) {
            Session::flash('error', 'No se encontró un perfil de cliente asociado a su cuenta. Por favor, complete su perfil.');
            return redirect()->route('dashboard');
        }

        // Obtener citas del cliente a través de sus mascotas
        $citas = Appointment::whereHas('mascota.cliente', function ($query) use ($cliente) {
            $query->where('id', $cliente->id);
        })->orderBy('date', 'desc')->get();

        return view('client.citas.index', compact('citas'));
    }

    /**
     * Muestra el formulario para crear una nueva cita.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $cliente = Auth::user()->cliente;

        // Si no hay perfil de cliente, abortar o redirigir
        if (!$cliente) {
            Session::flash('error', 'Debe tener un perfil de cliente para agendar citas.');
            abort(403, 'Debe tener un perfil de cliente para agendar citas.');
        }

        $mascotas = $cliente->mascotas;
        $allServices = Service::all();

        // Obtener IDs de servicios comprados por el cliente con estado 'COMPLETED'
        // Esto asume una relación 'purchasedServices' en el modelo Cliente que apunta a ServiceOrder
        // Si no existe, deberías obtenerlo directamente del modelo ServiceOrder:
        $purchasedServiceIds = ServiceOrder::where('user_id', Auth::id())
                                            ->where('status', 'COMPLETED')
                                            ->pluck('service_id')
                                            ->toArray();
        
        $veterinarians = Veterinarian::with('user')->get();

        return view('client.citas.create', compact('mascotas', 'allServices', 'purchasedServiceIds', 'veterinarians'));
    }

    /**
     * Almacena una nueva cita en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            return redirect()->back()->with('error', 'No se encontró el perfil de cliente asociado.');
        }

        // 1. Validar los datos del formulario
        $validatedData = $request->validate([
            'mascota_id' => [
                'required',
                'exists:mascotas,id',
                // Asegúrate de que la mascota pertenece al cliente autenticado
                Rule::exists('mascotas', 'id')->where(function ($query) use ($cliente) {
                    $query->where('cliente_id', $cliente->id);
                }),
            ],
            'veterinarian_id' => 'required|exists:veterinarians,id',
            'date' => 'required|date|after_or_equal:now',
            'reason' => 'nullable|string|max:255',
            'service_id' => 'required|exists:services,id',
        ]);

        $selectedServiceId = $validatedData['service_id'];

        // 2. RECUPERAR EL OBJETO SERVICE AQUÍ (¡CRÍTICO para evitar 'Undefined variable $service'!)
        $service = Service::findOrFail($selectedServiceId);

        // 3. Verificar si el cliente ya ha comprado y completado el pago de este servicio
        $hasPurchasedService = ServiceOrder::where('user_id', Auth::id())
                                            ->where('service_id', $selectedServiceId)
                                            ->where('status', 'COMPLETED')
                                            ->exists();

        // 4. Lógica condicional: si el servicio NO ha sido comprado/pagado
        if (!$hasPurchasedService) {
            // El servicio NO ha sido comprado/pagado, redirigir a pasarela de pago

            // Almacenar los datos de la cita en la sesión temporalmente
            Session::put('pending_appointment_data', $validatedData);

            // También necesitamos el ID del servicio y el ID del usuario para el flujo de pago/callback
            Session::put('service_to_purchase_id', $selectedServiceId);
            Session::put('user_id_for_purchase', $cliente->user_id); // Guardar el user_id

            // Redirigir a tu ruta de pago (ahora $service está definida)
            return redirect()->route('payments.show_checkout_page', ['service' => $service->id])
                             ->with('info', 'El servicio tiene un costo. Por favor, completa el pago para confirmar tu cita.');
        }

        // 5. Lógica condicional: El servicio YA ha sido comprado/pagado, proceder directamente con el agendamiento
        $appointment = Appointment::create([
            'mascota_id' => $validatedData['mascota_id'],
            'veterinarian_id' => $validatedData['veterinarian_id'],
            'date' => Carbon::parse($validatedData['date']), // Convertir a objeto Carbon
            'reason' => $validatedData['reason'],
            'service_id' => $validatedData['service_id'],
            'status' => 'pending', // La cita inicia como pendiente
        ]);

        return redirect()->route('client.citas.index')->with('success', 'Cita confirmada, hemos recepcionado un comprobante de tu cita agendada a tu correo, Gracias.');
    }

    /**
     * Maneja el callback de pago exitoso (llamado después de que PayPal confirma el pago).
     * Esta es la función que debe ser el "return URL" o "webhook" de tu PayPal para completar la cita.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeBookingAfterPayment(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Recuperar los datos pendientes de la sesión
        $pendingAppointmentData = Session::get('pending_appointment_data');
        $serviceToPurchaseId = Session::get('service_to_purchase_id');
        $userIdForPurchase = Session::get('user_id_for_purchase');

        // Validar que tenemos los datos necesarios
        if (!$pendingAppointmentData || !$serviceToPurchaseId || !$userIdForPurchase) {
            return redirect()->route('client.citas.create')->with('error', 'No se pudo completar la reserva. Datos de cita pendientes no encontrados o sesión expirada. Intente de nuevo.');
        }

        // Obtener el cliente actual (para seguridad)
        $cliente = Auth::user()->cliente;
        if (!$cliente || $cliente->user_id !== $userIdForPurchase) {
            return redirect()->route('client.citas.create')->with('error', 'Error de seguridad al procesar la cita. Intente de nuevo.');
        }

        // --- LÓGICA DE CONFIRMACIÓN DE PAGO REAL ---
        // Aquí es donde en un sistema real, verificarías la transacción con PayPal
        // usando el paypal_order_id que PayPal te debería enviar en el callback.
        // Por ahora, como dijiste que PayPal ya funciona, asumimos que:
        // 1. Ya tienes una ServiceOrder con 'status' 'COMPLETED' creada por tu webhook/callback de PayPal.
        //    O, si este es el callback final, tú mismo la creas/actualizas aquí.

        // EJEMPLO: Verificar si la ServiceOrder ya fue completada por el sistema PayPal.
        // Esto es crucial para no crear una nueva ServiceOrder si PayPal ya lo hizo.
        $serviceOrder = ServiceOrder::where('user_id', $userIdForPurchase)
                                     ->where('service_id', $serviceToPurchaseId)
                                     ->where('status', 'COMPLETED') // Busca una orden ya COMPLETED
                                     ->first();

        if (!$serviceOrder) {
            // Si no se encuentra una orden COMPLETADA, significa que el flujo de pago real no la registró aún,
            // o estamos en un escenario de simulación pura.
            // Aquí podrías crearla o actualizarla para simular el éxito, si no lo hace tu webhook real de PayPal.
            // Para la simulación, la creamos/actualizamos aquí con status COMPLETED.
            // Es importante que el 'amount' provenga del servicio real, no de la sesión.
            $servicePrice = Service::find($serviceToPurchaseId)->price ?? 0;

            $serviceOrder = ServiceOrder::updateOrCreate(
                [
                    'user_id' => $userIdForPurchase,
                    'service_id' => $serviceToPurchaseId,
                    'status' => 'PENDING', // Buscar una orden pendiente existente
                    // Considera añadir un identificador único de transacción aquí si ya lo tienes
                ],
                [
                    'status' => 'COMPLETED',
                    'amount' => $servicePrice,
                    'currency' => 'USD', // Ajusta tu moneda
                    'paypal_order_id' => 'SIMULATED_PAYPAL_ID_' . uniqid(), // ID simulado
                    'payment_details' => json_encode(['simulated' => true, 'date' => now()]),
                ]
            );

            // Si después de updateOrCreate, el status no es COMPLETED (lo cual solo pasaría si era PENDING y se actualizó),
            // se fuerza a COMPLETED. Esto es redundante si updateOrCreate ya lo hace, pero asegura.
            if ($serviceOrder->status !== 'COMPLETED') {
                $serviceOrder->status = 'COMPLETED';
                $serviceOrder->save();
            }
        }

        // 2. Crear la cita utilizando los datos almacenados en la sesión
        $appointment = Appointment::create([
            'mascota_id' => $pendingAppointmentData['mascota_id'],
            'veterinarian_id' => $pendingAppointmentData['veterinarian_id'],
            'date' => Carbon::parse($pendingAppointmentData['date']),
            'reason' => $pendingAppointmentData['reason'],
            'service_id' => $pendingAppointmentData['service_id'],
            'status' => 'pending', // La cita inicia como pendiente
        ]);

        // Limpiar los datos temporales de la sesión
        Session::forget('pending_appointment_data');
        Session::forget('service_to_purchase_id');
        Session::forget('user_id_for_purchase');

        return redirect()->route('client.citas.index')->with('success', '¡Pago procesado y cita agendada exitosamente! Hemos recepcionado un comprobante de tu cita agendada a tu correo, Gracias.');
    }
}