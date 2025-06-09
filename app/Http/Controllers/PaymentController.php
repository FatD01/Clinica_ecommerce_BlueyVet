<?php

namespace App\Http\Controllers; // ¡Asegúrate de que sea este namespace!

use Illuminate\Http\Request;
use App\Models\Service;        // Necesitas importar tu modelo Service
use App\Models\ServiceOrder;   // Necesitas importar tu modelo ServiceOrder
use App\Models\Veterinarian;  // Necesitas importar tu modelo Veterinarian para el formulario de cita
use Illuminate\Support\Facades\Log; // Para depuración
use Illuminate\Support\Facades\Mail; // ¡Para enviar el correo de cita (post-pago)!
use App\Mail\ServiceContactMail; // ¡La plantilla de correo!
use Illuminate\Support\Facades\Auth; // ¡¡¡Necesario para Auth::id()!!!

class PaymentController extends Controller
{
    /**
     * Muestra la página de checkout con los botones de PayPal.
     * Recibe los datos del servicio seleccionado desde la página de servicios.
     */
    public function checkout(Request $request)
    {
        // Validar que los datos del servicio son correctos
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'amount' => 'required|numeric|min:0.01',
            'service_name' => 'required|string',
        ]);

        $service = Service::find($request->service_id);

        // Seguridad: Validar que el precio enviado coincide con el precio real del servicio
        // Esto evita que un usuario manipule el precio en el cliente
        if ($service->price != $request->amount) {
            Log::warning('Intento de manipulación de precio detectado para servicio_id: ' . $service->id . ' - Usuario: ' . (Auth::id() ?? 'Invitado')); // Usando Auth::id()
            return redirect()->back()->with('error', 'Hubo un problema con el precio del servicio. Por favor, intenta de nuevo.');
        }

        // Crear una "orden" provisional en tu base de datos con estado 'pending'
        // Esto registra que un usuario ha iniciado un intento de pago
        $order = ServiceOrder::create([
            'user_id' => Auth::id(), // ¡Usando Auth::id()!
            'service_id' => $service->id,
            'amount' => $service->price,
            'status' => 'pending', // Estado inicial de la orden
            // 'paypal_order_id' y 'payment_details' se llenarán después del pago real de PayPal
        ]);

        // Pasa la información a la vista donde se renderizarán los botones de PayPal
        return view('client.checkout', [
            'service' => $service,
            'order' => $order, // Pasamos la orden provisional
        ]);
    }

    /**
     * Maneja el éxito del pago de PayPal.
     * Esta ruta será llamada por el JavaScript de PayPal en el navegador después de un pago exitoso.
     */
    public function success(Request $request)
    {
        // NOTA: En una aplicación real, aquí harías una verificación del pago con la API de PayPal
        // para asegurarte de que la transacción es genuina y completa.
        // Por ahora, asumimos que si llega aquí, PayPal ha aprobado el pago.

        $orderId = $request->query('orderId'); // Recuperamos el ID de nuestra ServiceOrder provisional
        $order = ServiceOrder::find($orderId);

        if ($order) {
            // Actualiza el estado de la orden a 'completed'
            $order->update([
                'status' => 'completed',
                // Aquí podrías guardar el ID de transacción de PayPal, etc., si lo tuvieras
                // 'paypal_order_id' => $request->query('paypal_order_id'), // Ejemplo
                // 'payment_details' => json_encode($request->all()), // Guardar todos los detalles de PayPal
            ]);

            // Redirige al cliente al formulario de cita, pasando la orden completada
            return redirect()->route('appointments.show_form', ['order' => $order->id])
                            ->with('success', '¡Pago completado con éxito! Ahora puedes reservar tu cita.');
        }

        // Si la orden no se encuentra, redirige a servicios con un error
        return redirect()->route('client.servicios.index')->with('error', 'No se pudo encontrar la orden de pago. Por favor, contacta a soporte.');
    }

    /**
     * Maneja la cancelación del pago de PayPal.
     * Esta ruta será llamada por el JavaScript de PayPal si el usuario cancela el pago.
     */
    public function cancel(Request $request)
    {
        $orderId = $request->query('orderId'); // Si PayPal te devuelve el orderId en la cancelación
        if ($orderId) {
            $order = ServiceOrder::find($orderId);
            if ($order) {
                $order->update(['status' => 'cancelled']); // Marca la orden como cancelada
            }
        }
        return redirect()->route('client.servicios.index')->with('error', 'El pago ha sido cancelado.');
    }

    /**
     * Muestra el formulario de cita después de un pago exitoso.
     * Recibe el ID de la ServiceOrder para pre-seleccionar el servicio.
     */
    public function showAppointmentForm(ServiceOrder $order)
    {
        // Seguridad: Asegurarse de que la orden esté 'completed' y pertenezca al usuario actual
        // Si no está completa o no es del usuario logueado, redirige
        if ($order->status !== 'completed' || $order->user_id !== Auth::id()) { // ¡Usando Auth::id()!
            return redirect()->route('client.servicios.index')->with('error', 'Acceso no autorizado al formulario de cita o pago no completado para esta orden.');
        }

        // Cargar los veterinarios para el selector en el formulario
        $veterinarians = Veterinarian::all();

        // Pasa los datos de la orden, el servicio asociado y los veterinarios a la vista
        return view('client.appointment_form', [
            'order' => $order,
            'service' => $order->service, // El servicio asociado a la orden (gracias a la relación en ServiceOrder model)
            'veterinarians' => $veterinarians,
        ]);
    }

    /**
     * Almacena los datos del formulario de cita (después del pago).
     * Esto será llamado por el formulario de cita después del pago.
     */
    public function storeAppointment(Request $request, ServiceOrder $order)
    {
        // Seguridad: Volver a verificar que la orden esté 'completed' y pertenezca al usuario actual
        if ($order->status !== 'completed' || $order->user_id !== Auth::id()) { // ¡Usando Auth::id()!
            return redirect()->back()->with('error', 'No se puede procesar la cita. La orden no está pagada o no te pertenece.');
        }

        // Validación de los datos del formulario de cita
        $validatedData = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            // Si el campo 'veterinario' es un ID, validarlo con 'exists:veterinarians,id'
            // Si es solo el nombre, 'string|max:255' está bien.
            'veterinario' => 'required|string|max:255', // Ajusta si es ID
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'mensaje' => 'nullable|string|max:1000',
            // 'privacy' => 'required|accepted', // Si el formulario de cita tiene checkbox de privacidad
        ]);

        // Aquí deberías crear la cita en tu base de datos de citas
        // Por ejemplo:
        // Appointment::create([
        //     'user_id' => Auth::id(), // O el ID del usuario de la orden
        //     'service_order_id' => $order->id,
        //     'veterinarian_id' => $request->veterinario, // Si guardas el ID del veterinario
        //     'date' => $validatedData['fecha'],
        //     'time' => $validatedData['hora'],
        //     'notes' => $validatedData['mensaje'],
        // ]);

        // ¡¡¡ENVÍO DE CORREO AQUÍ!!!
        Mail::to('fatima.rodriguez@tecsup.edu.pe')->send(new ServiceContactMail($validatedData)); // <--- ¡Esta es la "webada" que necesitas!

        return redirect()->route('client.home')->with('success', '¡Tu cita ha sido agendada y tu solicitud enviada! Nos pondremos en contacto pronto para confirmarla.');
    }
}