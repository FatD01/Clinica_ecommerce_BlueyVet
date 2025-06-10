<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\CitaController;
use App\Http\Controllers\Client\ServicioController;
use App\Http\Controllers\PaymentController; // Asegúrate de que esta línea esté presente

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

require __DIR__.'/auth.php';