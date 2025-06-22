<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Promotion;
use App\Http\Controllers\Client\CartController; // Asegúrate de importar tu CartController
use Illuminate\Support\Facades\Log; // ¡Importante: Importar el Facade de Log!

class CartComposer
{
    protected $cartController;

    public function __construct(CartController $cartController)
    {
        $this->cartController = $cartController;
    }

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        Log::info('DEBUG: [CartComposer] - Método compose iniciado.'); // Log 1: Composer iniciado

        $revalidatedData = $this->cartController->revalidateAndCalculateCart(Session::get('cart', []));
        $cart = $revalidatedData['cart'];
        $total = $revalidatedData['total'];

        $cliente = null;
        Log::info('DEBUG: [CartComposer] - Valor inicial de $cliente: ' . ($cliente === null ? 'null' : 'no null (error log)')); // Log 2: $cliente inicial

        if (Auth::check()) {
            Log::info('DEBUG: [CartComposer] - Usuario autenticado. Intentando obtener Cliente.'); // Log 3: Usuario autenticado
            $user = Auth::user();
            try {
                $cliente = $user->cliente; // Asegúrate de que esta relación exista en tu modelo User
                if ($cliente) {
                    Log::info('DEBUG: [CartComposer] - Cliente obtenido: ID ' . $cliente->id . ', Dirección: ' . ($cliente->direccion ?? 'N/A')); // Log 4: Cliente obtenido
                } else {
                    Log::info('DEBUG: [CartComposer] - Cliente NO encontrado para el usuario autenticado (relación es null).'); // Log 5: Cliente no encontrado
                }
            } catch (\Exception $e) {
                Log::error('ERROR: [CartComposer] - Error al obtener la relación cliente: ' . $e->getMessage()); // Log 6: Error en la relación
                $cliente = null; // Asegúrate de que $cliente sea null si hay un error
            }
        } else {
            Log::info('DEBUG: [CartComposer] - Usuario NO autenticado.'); // Log 7: Usuario no autenticado
        }

        $view->with('cart', $cart)
             ->with('total', $total)
             ->with('cliente', $cliente); // <-- ¡Aquí se pasa $cliente a la vista!

        Log::info('DEBUG: [CartComposer] - Método compose finalizado. $cliente se pasó a la vista como: ' . ($cliente ? 'objeto Cliente' : 'null')); // Log 8: Composer finalizado
    }
}