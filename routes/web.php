<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\CitaController;
use App\Http\Controllers\Client\ServicioController;
use App\Http\Controllers\Client\MascotaController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\products\Petshop\ProductController;
// use App\Http\Controllers\Client\ProductPaymentController;


use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ProductPaymentController;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\VeterinarianController;
use App\Http\Controllers\HistorialMedicoController;

// Importa el nuevo BlogController
use App\Http\Controllers\BlogController; // ¡Importante: Añadir esta línea!
use App\Http\Controllers\FaqController;


Route::get('/client/home', [HomeController::class, 'index'])->name('client.home');

Route::get('/', function () {
    return view('client.welcome');
});

// Rutas de servicios (públicas)
Route::prefix('/servicios')->name('client.servicios.')->group(function () {
    Route::get('/', [ServicioController::class, 'index'])->name('index');
});

Route::get('/dashboard', function () {
    return view('client.welcome');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de usuario autenticado
Route::middleware(['auth'])->group(function () {

    // Rutas de perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de gestión de mascotas para el cliente
    Route::prefix('mi-mascota')->name('client.mascotas.')->group(function () {
        Route::get('/', [MascotaController::class, 'index'])->name('index');
        Route::get('/registrar', [MascotaController::class, 'create'])->name('create');
        Route::post('/', [MascotaController::class, 'store'])->name('store');
        Route::get('/{mascota}/editar', [MascotaController::class, 'edit'])->name('edit');
        Route::put('/{mascota}', [MascotaController::class, 'update'])->name('update');
        Route::delete('/{mascota}', [MascotaController::class, 'destroy'])->name('destroy');
    });

    // Rutas de gestión de citas para el cliente
    Route::prefix('/citas')->name('client.citas.')->group(function () {
        Route::get('/', [CitaController::class, 'index'])->name('index');
        Route::get('/agendar', [CitaController::class, 'create'])->name('create');
        Route::post('/', [CitaController::class, 'store'])->name('store');
        // Ruta para el callback final de pago exitoso (si la ServiceOrder era para una cita)
        Route::get('/complete-booking', [CitaController::class, 'completeBookingAfterPayment'])->name('complete_booking');
        Route::get('/{appointment}', [CitaController::class, 'show'])->name('show'); // El nombre será 'client.citas.show'
    });

    // Bloque de Pagos con PayPal
    Route::prefix('payments')->name('payments.')->group(function () {
        // Ruta para mostrar la página de checkout con el ID de la ServiceOrder
        // Esto espera un ID de ServiceOrder EXISTENTE
        Route::get('/checkout/{serviceOrderId}', [PaymentController::class, 'showCheckoutPage'])->name('show_checkout_page');

        // NUEVA RUTA para la compra directa de servicios desde la vista de servicios (solicitud POST)
        // Esta ruta CREA la ServiceOrder primero.
        Route::post('/purchase-service', [PaymentController::class, 'purchaseService'])->name('purchase_service');

        // Ruta llamada por PayPal JS para crear la orden en PayPal (API)
        Route::post('/create-order', [PaymentController::class, 'checkout'])->name('checkout');

        // Ruta de retorno de PayPal para pago exitoso (PaymentController@success procesa y redirige)
        Route::get('/success', [PaymentController::class, 'success'])->name('success');
        // Ruta de retorno de PayPal para pago cancelado
        Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel');

        // Rutas para las VISTAS de estado de pago (estas son vistas finales genéricas)
        // Route::view('/success-page', 'client.checkout.success')->name('success_page');
        Route::get('/transaction-confirmed/{service_order_id}', [PaymentController::class, 'showCompletedTransactionView'])->name('transaction_confirmed_view');
        Route::view('/failed', 'client.checkout.failed')->name('failed');
        Route::view('/error-page', 'client.checkout.error')->name('error_page');
    });


    // --- NUEVO BLOQUE DE PAGOS CON PAYPAL PARA PRODUCTOS DEL CARRITO ---
    // Estas rutas serán manejadas por el ProductPaymentController
    Route::prefix('cart-payments')->name('cart_payments.')->group(function () {
        // Ruta para iniciar el pago desde el carrito
        // Será un POST desde el botón "Pagar con PayPal" en la vista del carrito
        Route::post('/pay', [ProductPaymentController::class, 'payWithPaypal'])->name('pay');

        // Rutas de retorno de PayPal (¡usarán los mismos nombres que tus rutas de servicio, pero dentro de este nuevo grupo!)
        Route::get('/success', [ProductPaymentController::class, 'paypalSuccess'])->name('success');
        Route::get('/cancel', [ProductPaymentController::class, 'paypalCancel'])->name('cancel');

        // Opcional: Ruta para ver los detalles de un pedido de producto completado
        // Route::get('/order-confirmed/{order_id}', [ProductPaymentController::class, 'showProductOrderConfirmedView'])->name('order_confirmed_view');
        // Route::get('/cart-payments/order-confirmed/{order}', [ProductPaymentController::class, 'orderConfirmed'])->name('cart-payments.order-confirmed');
        Route::get('/order-details/{order}', [ProductPaymentController::class, 'showProductOrderConfirmedView'])->name('order_details');

    });

}); // Fin del grupo de middleware 'auth'

// Rutas de Socialite de Google OAuth (Estas DEBEN estar fuera del middleware 'auth')
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google.redirect');


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

Route::get('/test-perf', function () {
    \Illuminate\Support\Facades\Log::info('PerfTest: /test-perf started');
    // No queries, no sessions, no views. Just return text.
    $response = 'Hello from simple route!';
    \Illuminate\Support\Facades\Log::info('PerfTest: /test-perf ended');
    return $response;
});



Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->user();
        $user = User::where('google_id', $googleUser->id)->first() ?? User::where('email', $googleUser->email)->first();

        if ($user) {
            Log::info('Usuario encontrado, vinculando ID de Google si es nuevo.', ['email' => $googleUser->email]);
            $user->google_id = $googleUser->id;
            $user->email_verified_at = now();
            $user->save();
        } else {
            Log::info('Creando nuevo usuario con datos de Google.', ['email' => $googleUser->email]);
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


//VETERINARIO
// Ruta raíz que redirige según el estado de autenticación
Route::get('/veterinario', function () {
    if (Auth::check()) {
        return redirect()->route('index'); // Dashboard veterinario
    } else {
        return redirect()->route('veterinarian.login'); // Login específico del veterinario
    }
});

// Vista del login exclusivo para veterinarios
Route::get('/veterinario/login', function () {
    return view('login'); // Blade personalizado para veterinario
})->name('veterinarian.login');

Route::post('/veterinario/login', [AuthController::class, 'login'])->name('veterinarian.login.submit');



// Rutas de autenticación (solo para invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas (solo para usuarios autenticados)
Route::middleware('auth')->group(function () {

    Route::get('/index', function () {
        $user = Auth::user();

        // Solo permitir si es veterinario
        if (strtolower($user->role) !== 'veterinario') {
            return redirect()->route('veterinarian.login'); // Redirige al login de veterinarios si no lo es
        }

        $veterinarian = $user->veterinarian;
        return view('index', compact('veterinarian'));
    })->name('index');

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Perfil del veterinario
    Route::get('/perfil', function () {
        $veterinarian = Auth::user()->veterinarian;
        return view('info', compact('veterinarian'));
    })->name('veterinarian.profile');

    Route::get('/perfil/editar/{id}', [VeterinarianController::class, 'edit'])->name('veterinarian.edit');
    Route::patch('/veterinario/perfil', [VeterinarianController::class, 'update'])->name('veterinarian.profile.update');

    // Citas
    Route::get('/citas-agendadas', [CitaController::class, 'citasAgendadas'])->name('veterinarian.citas');
    Route::get('/citasagendadas/cliente/{id}', [CitaController::class, 'verMascotas'])->name('ver.mascotas');
    Route::get('/cita/{id}/atender', [VeterinarianController::class, 'formularioAtencion'])->name('veterinarian.atender');
    Route::post('/guardar-atencion', [VeterinarianController::class, 'guardarAtencion'])->name('veterinarian.guardar.atencion');

    // Historial médico (desde controlador dedicado)
    Route::get('/historiales', [HistorialMedicoController::class, 'index'])->name('historialmedico.index');
    Route::get('/historiales/{id}', [HistorialMedicoController::class, 'verHistorial'])->name('historialmedico.show');

    // NUEVA RUTA: Historial médico por mascota (desde controlador VeterinarianController)
    Route::get('/veterinarian/historial/{mascota}', [VeterinarianController::class, 'verHistorial'])->name('veterinarian.historial');
});


// --- NUEVAS RUTAS DEL BLOG Y FAQ (PÚBLICAS) ---
// Estas rutas son para el acceso de los clientes y visitantes.
// Se colocan fuera del middleware 'auth' para que sean accesibles a todos.
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{post:slug}', [BlogController::class, 'show'])->name('show');
});

// Ruta específica para la sección de Preguntas Frecuentes (FAQ)
Route::get('/preguntas-frecuentes', [FaqController::class, 'index'])->name('faqs.index'); 
// --- FIN DE NUEVAS RUTAS DEL BLOG Y FAQ ---


require __DIR__.'/auth.php';