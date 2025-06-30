<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()


    {




        $recentPosts = Post::where('type', 'blog')
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();
  



        // Paso 1: Obtener los IDs de los 3 servicios más comprados con estado 'completed'.
        $topServiceIds = DB::table('service_orders') // Usa el nombre exacto de tu tabla: 'services_orders'
            ->select('service_id')
            ->where('status', 'completed') // **Filtra solo por órdenes completadas**
            ->groupBy('service_id')
            ->orderByDesc(DB::raw('COUNT(service_id)')) // Ordena de mayor a menor según el conteo de service_id
            ->limit(3) // Limita el resultado a los 3 primeros
            ->pluck('service_id'); // Obtiene solo una colección de los IDs de los servicios

        // Paso 2: Cargar los objetos Service completos correspondientes a esos IDs.
        $featuredServices = Service::whereIn('id', $topServiceIds)->get();

        // Paso 3 (Opcional): Si hay menos de 3 servicios comprados (o ninguno),
        // puedes rellenar el resto con otros servicios para que siempre se muestren 3 tarjetas.
        if ($featuredServices->count() < 3) {
            $remaining = 3 - $featuredServices->count();
            // Obtener servicios que NO estén en la lista de los top comprados
            // Puedes ajustar el 'orderBy' de esta consulta (ej. 'name', 'id')
            $defaultServices = Service::whereNotIn('id', $topServiceIds)
                ->limit($remaining)
                ->get();
            // Combina los servicios comprados con los servicios por defecto
            $featuredServices = $featuredServices->merge($defaultServices);
        }

        // Paso 4: Pasa la colección de servicios destacados a tu vista.
        return view('client.welcome', compact('recentPosts', 'featuredServices')); // **Cambia 'your_view_name' por el nombre de tu archivo Blade**
    }
}
