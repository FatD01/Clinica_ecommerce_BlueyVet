<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\CitaController;
use App\Http\Controllers\Client\ServicioController;
use App\Http\Controllers\Client\MascotaController;
use App\Http\Controllers\PaymentController;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


use App\Http\Controllers\Client\CartController; //fabricio estuvo aquí
use App\Http\Controllers\Client\Products\Petshop\ProductController;

Route::get('/client/home', [HomeController::class, 'index'])->name('client.home');

Route::get('/', function () {
    return view('client.welcome');
});

// Las rutas de servicios pueden estar fuera del 'auth' si quieres que sean públicas
Route::prefix('/servicios')->name('client.servicios.')->group(function () {
    Route::get('/', [ServicioController::class, 'index'])->name('index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ESTE ES EL BLOQUE CRÍTICO: TODAS las rutas del cliente que requieren sesión
Route::middleware(['auth'])->group(function () {

    // Rutas del perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- BLOQUE DE RUTAS PARA GESTIÓN DE MASCOTAS POR EL CLIENTE ---
    Route::prefix('mi-mascota')->name('client.mascotas.')->group(function () {
        Route::get('/', [MascotaController::class, 'index'])->name('index');
        Route::get('/registrar', [MascotaController::class, 'create'])->name('create');
        Route::post('/', [MascotaController::class, 'store'])->name('store');
        Route::get('/{mascota}/editar', [MascotaController::class, 'edit'])->name('edit');
        Route::put('/{mascota}', [MascotaController::class, 'update'])->name('update');
        Route::delete('/{mascota}', [MascotaController::class, 'destroy'])->name('destroy');
    });
    // --- FIN BLOQUE DE RUTAS PARA GESTIÓN DE MASCOTAS ---

    // --- BLOQUE DE RUTAS PARA GESTIÓN DE CITAS POR EL CLIENTE (AHORA DENTRO DE AUTH) ---
    Route::prefix('/citas')->name('client.citas.')->group(function () {
        Route::get('/', [CitaController::class, 'index'])->name('index');
        Route::get('/agendar', [CitaController::class, 'create'])->name('create');
        Route::post('/', [CitaController::class, 'store'])->name('store');
    });
    // --- FIN BLOQUE DE RUTAS DE CITAS ---

    // --- BLOQUE DE PAGOS PAYPAL (Ya estaban aquí, asegúrate de que sigan) ---
    Route::get('/payments/show-checkout/{serviceOrderId}', [PaymentController::class, 'showCheckoutPage'])->name('payments.show_checkout_page');
    Route::post('/payments/create-order', [PaymentController::class, 'checkout'])->name('payments.create_order');
    Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

    // --- Rutas para las VISTAS DE ESTADO DEL PAGO ---
    Route::get('/checkout/success', function() {
        return view('client.payment_success');
    })->name('checkout.success_page');

    Route::get('/checkout/failed', function() {
        return view('client.payment_cancelled');
    })->name('checkout.failed');

    Route::get('/checkout/error', function() {
        return view('client.payment_error');
    })->name('checkout.error_page');

    // --- Rutas para el formulario de cita después del pago (si aplica) ---
    Route::get('/reservar-cita/{order}', [PaymentController::class, 'showAppointmentForm'])->name('appointments.show_form');
    Route::post('/reservar-cita/{order}', [PaymentController::class, 'storeAppointment'])->name('appointments.store');
}); // <-- CIERRE DEL GRUPO DE MIDDLEWARE 'auth'

// Rutas para autenticación con Google Socialite (Estas DEBEN estar fuera del middleware 'auth')
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google.redirect');

Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->user();
        $user = User::where('google_id', $googleUser->id)->first() ?? User::where('email', $googleUser->email)->first();

        if ($user) {
            Log::info('User found, linking Google ID if new.', ['email' => $googleUser->email]);
            $user->google_id = $googleUser->id;
            $user->email_verified_at = now();
            $user->save();
        } else {
            Log::info('Creating new user with Google data.', ['email' => $googleUser->email]);
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(),
                'role' => 'Cliente',
            ]);
        }

        Auth::login($user);
        return redirect()->route('client.home')->with('success', '¡Has iniciado sesión con Google correctamente!');

    } catch (\Exception $e) {
        Log::error('Error al iniciar sesión con Google: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return redirect('/login')->with('error', 'No se pudo iniciar sesión con Google. Inténtalo de nuevo.');
    }
});



// Rutas de productos y carrito (las que definiste y se mantienen)
// Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
// Route::post('/cart/update/{product}', [CartController::class, 'update'])->name('cart.update');
// Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');

// // 2. Luego: ruta dinámica de productos por categoría
// Route::get('/productos/categoria/{id}', [ProductController::class, 'porCategoriaPadre'])->name('productos.por_categoria');

// // Ruta para obtener el componente del carrito flotante (la que ya tenías)
// Route::get('/cart/component', function () {
//     return response()->json([
//         'html' => view('components.cart-floating')->render()
//     ]);
// });


// Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
// Route::post('/cart/update/{product}', [CartController::class, 'update'])->name('cart.update');
// Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
// // 2. Luego: ruta dinámica de productos por categoría
// Route::get('/productos/categoria/{id}', [ProductController::class, 'porCategoriaPadre'])->name('productos.por_categoria');
// // Ruta para obtener el componente del carrito flotante (¡AHORA USA EL MÉTODO DEL CONTROLADOR!)
// Route::get('/cart/component', [CartController::class, 'getCartComponent'])->name('cart.component');


// Rutas del carrito (mantener como están)
Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/component', [CartController::class, 'getCartComponent'])->name('cart.component');

// Ruta principal para la página de productos de Petshop (ej. /productos/petshop)
// Esta ruta apunta al método 'index' que muestra Petshop por defecto.
Route::get('/productos/petshop', [ProductController::class, 'index'])->name('client.products.petshop');

// Ruta dinámica para productos por categoría (ej. /productos/categoria/1 o /productos/categoria/3)
// Esta es la ruta a la que apuntan los enlaces del navbar y el formulario de filtro.
Route::get('/productos/categoria/{id}', [ProductController::class, 'porCategoriaPadre'])->name('productos.por_categoria');

// ... otras rutas de tu aplicación



require __DIR__ . '/auth.php';
