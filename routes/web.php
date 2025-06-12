<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\CitaController;
use App\Http\Controllers\Client\ServicioController;
use App\Http\Controllers\PaymentController; // ¡Importa este PaymentController de la raíz!

use App\Http\Controllers\Client\CartController; //fabricio estuvo aquí
use App\Http\Controllers\Client\Products\Petshop\ProductController;

Route::get('/client/home', [HomeController::class, 'index'])->name('client.home');

Route::get('/', function () {
    return view('client.welcome');
});

Route::prefix('/citas')->name('client.citas.')->group(function () {
    Route::get('/', [CitaController::class, 'index'])->name('index');
    // Si quieres un formulario de cita general / contacto aquí,
    // su ruta POST iría aquí (ej. Route::post('/send', [CitaController::class, 'sendContactMail'])->name('send');)
});

Route::prefix('/servicios')->name('client.servicios.')->group(function () {
    Route::get('/', [ServicioController::class, 'index'])->name('index');
    // ¡LA RUTA 'send-contact-mail' QUE APUNTABA A ServicioController DEBE SER ELIMINADA DE AQUÍ!
    // No la pegues en este archivo
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// **ESTE ES EL BLOQUE CRÍTICO PARA LAS RUTAS DE PAGO Y CITA DESPUÉS DEL PAGO**
Route::middleware(['auth'])->group(function () {
    // Rutas del perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // RUTAS RELACIONADAS CON PAGOS DE SERVICIOS
    Route::post('/payments/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

    // RUTAS PARA EL FORMULARIO DE CITA DESPUÉS DEL PAGO
    Route::get('/reservar-cita/{order}', [PaymentController::class, 'showAppointmentForm'])->name('appointments.show_form');
    Route::post('/reservar-cita/{order}', [PaymentController::class, 'storeAppointment'])->name('appointments.store');
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
