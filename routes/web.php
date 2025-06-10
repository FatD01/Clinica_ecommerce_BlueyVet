<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\CitaController;
use App\Http\Controllers\Client\ServicioController;
use App\Http\Controllers\PaymentController; // Asegúrate de que esta línea esté presente

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // Asegúrate de importar Hash
use Illuminate\Support\Facades\Log; // Importa Log para evitar el error de tipo indefinido

Route::get('/client/home', [HomeController::class, 'index'])->name('client.home');

Route::get('/', function () {
    return view('client.welcome');
});

Route::prefix('/citas')->name('client.citas.')->group(function () {
    Route::get('/', [CitaController::class, 'index'])->name('index');
});

Route::prefix('/servicios')->name('client.servicios.')->group(function () {
    Route::get('/', [ServicioController::class, 'index'])->name('index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Rutas del perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- BLOQUE DE PAGOS PAYPAL ---

    // 1. RUTA PARA MOSTRAR LA PÁGINA DE CHECKOUT (GET)
    // El usuario es redirigido aquí para ver los detalles del pago antes de proceder con PayPal.
    // Requiere un 'service' para mostrar la información del servicio.
    Route::get('/payments/show-checkout/{service}', [PaymentController::class, 'showCheckoutPage'])->name('payments.show_checkout_page');

    // 2. RUTA PARA CREAR LA ORDEN DE PAYPAL (POST - Llamada por el JS del botón de PayPal)
    // Esta es una llamada AJAX desde el frontend para generar el ID de la orden en PayPal.
    Route::post('/payments/create-order', [PaymentController::class, 'checkout'])->name('payments.create_order'); // Cambié el nombre a 'create_order' para mayor claridad

    // 3. RUTA DE ÉXITO DE PAYPAL (GET - Redirección de PayPal)
    // PayPal redirige al usuario a esta URL después de una aprobación exitosa.
    // Aquí tu backend debe capturar el pago.
    Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');

    // 4. RUTA DE CANCELACIÓN DE PAYPAL (GET - Redirección de PayPal)
    // PayPal redirige al usuario a esta URL si cancela el pago o si hay un fallo antes de la captura.
    Route::get('/payments/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

    // --- Rutas para las VISTAS DE ESTADO DEL PAGO ---
    // Estas son las páginas a las que tu controlador Laravel redirigirá al usuario
    // después de procesar el éxito, cancelación o error.
    Route::get('/checkout/success', function() {
        return view('client.payment_success'); // Crea esta vista
    })->name('checkout.success_page');

    Route::get('/checkout/failed', function() {
        return view('client.payment_cancelled'); // O la vista que ya uses para cancelados/fallidos
    })->name('checkout.failed');

    Route::get('/checkout/error', function() {
        return view('client.payment_error'); // Crea esta vista para errores generales
    })->name('checkout.error_page');

    // --- Rutas para el formulario de cita después del pago (si aplica) ---
    // Asegúrate de que el 'order' que pasas aquí sea tu 'ServiceOrder' local.
    Route::get('/reservar-cita/{order}', [PaymentController::class, 'showAppointmentForm'])->name('appointments.show_form');
    Route::post('/reservar-cita/{order}', [PaymentController::class, 'storeAppointment'])->name('appointments.store');
});

// Rutas para autenticación con Google Socialite
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google.redirect');

Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->user();

        // Buscar al usuario por su ID de Google
        $user = User::where('google_id', $googleUser->id)->first();

        if ($user) {
                \Illuminate\Support\Facades\Log::info('User found by email, linking Google ID.', ['email' => $googleUser->email]);
                $user->google_id = $googleUser->id;
                $user->email_verified_at = now();
                $user->save();
        } else {
                \Illuminate\Support\Facades\Log::info('Creating new user with Google data.', ['email' => $googleUser->email]);
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'role' => 'Cliente', // <--- ¡AÑADE ESTA LÍNEA AQUÍ! O el rol que consideres por defecto.
                                        // Asegúrate de que este 'client' exista en tu lógica de roles si tienes.
                ]);
        }
        Auth::login($user); // Iniciar sesión al usuario

        // Redirige a la página de inicio del cliente (client.welcome como acordamos)
        return redirect()->route('client.home')->with('success', '¡Has iniciado sesión con Google correctamente!');

    } catch (\Exception $e) {
        Log::error('Error al iniciar sesión con Google: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return redirect('/login')->with('error', 'No se pudo iniciar sesión con Google. Inténtalo de nuevo.');
    }
});

require __DIR__.'/auth.php';