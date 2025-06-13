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

}); // Fin del grupo de middleware 'auth'

// Rutas de Socialite de Google OAuth (Estas DEBEN estar fuera del middleware 'auth')
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google.redirect');

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

require __DIR__.'/auth.php';