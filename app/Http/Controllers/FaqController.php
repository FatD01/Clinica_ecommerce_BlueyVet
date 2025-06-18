<?php

namespace App\Http\Controllers;

use App\Models\Post; // Importa tu modelo Post
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Muestra una lista de las Preguntas Frecuentes.
     */
    public function index()
    {
        // Recupera solo los posts que son de tipo 'faq' y están publicados
        // Ordenados por fecha de publicación (más recientes primero)
        $faqs = Post::where('type', 'faq')
                    ->where('is_published', true)
                    ->orderBy('published_at', 'desc')
                    ->get(); // O usa ->paginate(10) si tienes muchas FAQs

        // Pasa las FAQs a la vista
        return view('client.faq.index', compact('faqs'));
    }

    /**
     * Muestra una FAQ específica (opcional, si quieres URLs para FAQs individuales).
     */
    public function show(string $slug)
    {
        $faq = Post::where('slug', $slug)
                    ->where('type', 'faq')
                    ->where('is_published', true)
                    ->firstOrFail(); // Busca la FAQ por slug, o lanza 404 si no la encuentra

        return view('faqs.show', compact('faq'));
    }
}