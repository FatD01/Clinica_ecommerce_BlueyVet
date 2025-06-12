<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User; // Importa el modelo User
use App\Observers\UserObserver;

use Illuminate\Support\Facades\View; // Importa la fachada View
use App\View\Composers\CartComposer; 

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        User::observe(UserObserver::class);
        // View::composer(['client.includes.navbar', 'components.cart-floating'], CartComposer::class);
        View::composer(
        ['client.includes.navbar', 'components.cart-floating', 'client.products.petshop'],
        CartComposer::class
    );
    }
}
