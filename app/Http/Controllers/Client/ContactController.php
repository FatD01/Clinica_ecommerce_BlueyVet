<?php

namespace App\Http\Controllers\Client; // Asegúrate de que el namespace sea correcto

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest; // Importa tu Form Request
use App\Models\ContactMessage; // Importa tu Modelo
use App\Mail\AdminContactMail; // Importa tu Mailable
use Illuminate\Support\Facades\Mail; // Importa la Facade Mail
use Illuminate\Support\Facades\Log; // Para depuración, opcional
use Exception; // Para manejar errores, opcional

class ContactController extends Controller
{
    /**
     * Procesa el envío del formulario de contacto.
     */
    public function store(ContactFormRequest $request)
    {
        try {
            // 1. Guardar el mensaje en la base de datos
            $contactMessage = ContactMessage::create($request->validated());

            // 2. Enviar correo al administrador
            // Define aquí la dirección de correo del administrador
            $adminEmail = env('ADMIN_EMAIL'); // Usa el email configurado en .env como admin email

            // Si quieres enviar a un email específico (ej. info@blueyvet.com), usa:
            // $adminEmail = 'info@blueyvet.com';

            Mail::to($adminEmail)->send(new AdminContactMail($contactMessage));

            // 3. Redirigir con mensaje de éxito
            return redirect()->back()->with('success', '¡Tu mensaje ha sido enviado con éxito! Te responderemos pronto.');

        } catch (Exception $e) {
            // Registrar el error para depuración
            Log::error('Error al procesar formulario de contacto: ' . $e->getMessage(), ['exception' => $e]);

            // Redirigir con mensaje de error
            return redirect()->back()->with('error', 'Hubo un problema al enviar tu mensaje. Por favor, inténtalo de nuevo más tarde.');
        }
    }
}