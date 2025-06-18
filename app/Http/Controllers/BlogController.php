<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Muestra la página principal del blog (con posts de tipo 'blog').
     */
    public function index()
    {
        // Obtener solo los posts publicados y de tipo 'blog', ordenados por fecha de publicación
        $posts = Post::where('is_published', true)
                     ->where('type', 'blog')
                     ->orderBy('published_at', 'desc')
                     ->paginate(6); // Paginación para mostrar 6 posts por página

        return view('client.blog.index', compact('posts'));
    }

    /**
     * Muestra una entrada de blog individual.
     */
    public function show(Post $post) // Route Model Binding
    {
        // Asegúrate de que el post esté publicado y sea de tipo 'blog'
        if (!$post->is_published || $post->type !== 'blog') {
            abort(404); // O redirigir con un mensaje de error
        }

        return view('client.blog.show', compact('post'));
    }

    /**
     * Muestra la página de Preguntas Frecuentes (FAQ).
     */
    public function faq()
    {
        // Obtener solo los posts publicados y de tipo 'faq'
        $faqs = Post::where('is_published', true)
                    ->where('type', 'faq')
                    ->orderBy('created_at', 'asc') // O el orden que prefieras para FAQs
                    ->get();

        return view('client.blog.faq', compact('faqs'));
    }

    // Si quieres un sistema de comentarios simple para el blog
    // public function storeComment(Request $request, Post $post) { ... }
}