<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View; // Ya la tienes, bien
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ServiceContactMail;
use App\Models\Veterinarian; // ¡IMPORTANTE: Importa tu modelo Veterinarian!
use App\Models\Service;      // ¡IMPORTANTE: Importa tu modelo Service!
use App\Models\User;         // Necesaria para la relación con Veterinarian

class ServicioController extends Controller
{
    /**
     * Muestra la página de servicios con datos de veterinarios y servicios.
     */
    public function index(): View
    {
        // 1. Recuperar todos los veterinarios de la base de datos.
        // Usamos 'with('user')' para cargar también la información del usuario relacionado
        // Esto es crucial para acceder al nombre del veterinario desde la tabla 'users'.
        $veterinarios = Veterinarian::with('user')->get();

        // 2. Recuperar todos los servicios de la base de datos.
        $servicios = Service::all();

        // 3. Pasar los datos a la vista 'client.servicios.index'
        return view('client.servicios.index', compact('veterinarios', 'servicios'));
    }

    /**
     * Envía la solicitud de cita por correo electrónico.
     */
    public function sendContactMail(Request $request)
    {
        // 1. Validar los datos del formulario
        $validatedData = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'servicio' => 'required|string|max:255',
            'veterinario' => 'required|string|max:255',
            'fecha' => 'required|date|after_or_equal:today', // La fecha no puede ser anterior a hoy
            'hora' => 'required|date_format:H:i',
            'mensaje' => 'nullable|string|max:1000',
            'privacy' => 'required|accepted', // Asegura que el checkbox de privacidad fue marcado
        ]);

        // 2. Preparar el correo y enviarlo
        try {
            // Reemplaza 'fatima.rodriguez@tecsup.edu.pe' con la dirección de correo a la que quieres que lleguen las solicitudes
            Mail::to('fatima.rodriguez@tecsup.edu.pe')->send(new ServiceContactMail($validatedData));

            // Puedes loguear el éxito para depuración si es necesario
            // \Log::info('Solicitud de cita por correo enviada desde servicios: ' . $validatedData['email']);

            // 3. Redirigir con un mensaje de éxito
            return redirect()->back()->with('success', '¡Tu solicitud de cita ha sido enviada! Te contactaremos pronto.');

        } catch (\Exception $e) {
            // 4. Manejar errores de envío de correo
            Log::error('Error al enviar solicitud de cita por correo desde servicios: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un problema al enviar tu solicitud. Por favor, intenta de nuevo más tarde.');
        }
    }
}