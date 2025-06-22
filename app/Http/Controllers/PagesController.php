<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Muestra la página "Sobre Nosotros".
     */
    public function about()
    {
        // Este método simplemente carga la vista Blade que crearemos en el siguiente paso.
        return view('client.pages.about'); // Indicamos que la vista estará en resources/views/client/pages/about.blade.php
    }

    public function contact()
    {
        return view('client.pages.contact'); // Aquí también indicamos la vista para la página de contacto
    }
    // Si en el futuro quieres añadir otras páginas estáticas (como una de contacto simple),
    // podrías añadir más métodos aquí.
}