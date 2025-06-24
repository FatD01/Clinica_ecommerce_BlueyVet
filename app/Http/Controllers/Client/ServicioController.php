<?php

namespace App\Http\Controllers\Client; // ¡Este es el namespace correcto!

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use App\Models\Service;
use App\Models\Veterinarian;

class ServicioController extends Controller
{
    /**
     * Muestra la página de servicios con datos de veterinarios y servicios.
     */
    public function index(): View
    {
        $veterinarios = Veterinarian::with('user')->get();
        $servicios = Service::all(); // Asegúrate de que la variable aquí sea $servicios
         $servicios = Service::with('specialties')->get();

        return view('client.servicios.index', compact('veterinarios', 'servicios'));
    }
    // ¡No debe haber NINGÚN otro método aquí (como sendContactMail)!
}