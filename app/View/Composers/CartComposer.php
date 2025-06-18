<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Http\Controllers\Client\CartController; // Importa tu CartController
class CartComposer
{
    // public function compose(View $view)
    // {
    //     $cartController = new CartController();
    //     $revalidatedData = $cartController->revalidateAndCalculateCart(session()->get('cart', []));
    //     $view->with('cart', $revalidatedData['cart']);
    //     $view->with('total', $revalidatedData['total']);
    // }


    // En CartComposer.php
    public function compose(View $view)
    {
        $cart = session()->get('cart', []);
        $cartController = app()->make(CartController::class);
        $revalidatedData = $cartController->revalidateAndCalculateCart($cart);

        $view->with('cart', $revalidatedData['cart']);
        $view->with('total', $revalidatedData['total']);
    }
}
