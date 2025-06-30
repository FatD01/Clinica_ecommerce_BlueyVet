<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Importar controladores de cliente
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\CitaController;
use App\Http\Controllers\Client\ServicioController;
use App\Http\Controllers\Client\MascotaController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\products\Petshop\ProductController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\ClientOrderController; // Para ClientOrdersController
use App\Http\Controllers\OrderExportController;


// Importar otros controladores
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductPaymentController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VeterinarianController;
use App\Http\Controllers\HistorialMedicoController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NotificationController;

// Laravel y otras clases necesarias
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

// use App\Http\Middleware\IsAdminMiddleware;

// Ruta para el panel de administración (puede ser un spa o una vista de entrada)
// Asegúrate de que tu lógica de admin SPA maneje sus propias rutas internamente


// routes/web.php






// Página de inicio del cliente
Route::get('/', [HomeController::class, 'index'])->name('client.home');

// Rutas de información general
Route::get('/about-us', [PagesController::class, 'about'])->name('about.us');
Route::get('/contact-us', [PagesController::class, 'contact'])->name('contact.us');
Route::post('/contact-us', [ContactController::class, 'store'])->name('contact.store');

// Rutas de servicios (públicas)
Route::prefix('/servicios')->name('client.servicios.')->group(function () {
    Route::get('/', [ServicioController::class, 'index'])->name('index');
   Route::get('/{service}', [ServicioController::class, 'show'])->name('show');
});

// Rutas del carrito de compras (aunque se interactúe con él, los productos no requieren autenticación para ver)
Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/component', [CartController::class, 'getCartComponent'])->name('cart.component');

// Rutas de productos de Petshop
Route::get('/productos/petshop', [ProductController::class, 'index'])->name('client.products.petshop');
Route::get('/productos/categoria/{id}', [ProductController::class, 'porCategoriaPadre'])->name('productos.por_categoria');

// Rutas del Blog
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{post:slug}', [BlogController::class, 'show'])->name('show');
});

// Rutas de Preguntas Frecuentes (FAQ)
Route::get('/preguntas-frecuentes', [FaqController::class, 'index'])->name('faqs.index');
Route::get('/preguntas-frecuentes/{faq}', [FaqController::class, 'show'])->name('faq.show');


Route::get('/politica-de-privacidad', function () {
    return view('client.privacy_policy');
})->name('privacy.policy');

Route::get('/terminos-de-servicio', function () {
    return view('client.terms_of_service');
})->name('terms.of.service');

// Rutas de Socialite de Google OAuth (DEBEN estar fuera del middleware 'auth' inicial,
// el login y registro se manejan en el callback)
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google.redirect');




Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->user();
        $user = User::where('google_id', $googleUser->id)->first();

        if (!$user) {
            $user = User::where('email', $googleUser->email)->first();
            if ($user) {
                if (($user->provider === 'email' || $user->provider === null) && $user->google_id === null) {
                    $user->google_id = $googleUser->id;
                    $user->provider = 'google';
                    $user->email_verified_at = now();
                    $user->save();
                    Log::info('Usuario existente vinculado con Google.', ['email' => $googleUser->email]);
                } else {
                    Log::warning('Intento de login con Google para email existente ya vinculado.', ['email' => $googleUser->email, 'existing_provider' => $user->provider]);
                    return redirect('/login')->with('error', 'Ya existe una cuenta con este email. Inicia sesión con tu método original.');
                }
            } else {
                Log::info('Creando nuevo usuario con datos de Google.', ['email' => $googleUser->email]);
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => null,
                    'provider' => 'google',
                    'email_verified_at' => now(),
                    'role' => 'Cliente',
                ]);
            }
        } else {
            Log::info('Login con Google: Usuario existente encontrado por google_id.', ['email' => $googleUser->email]);
            if ($user->provider !== 'google') {
                $user->provider = 'google';
                $user->save();
            }
        }

        Auth::login($user);
        return redirect()->route('client.home')->with('success', '¡Has iniciado sesión con Google correctamente!');
    } catch (\Exception $e) {
        Log::error('Error al iniciar sesión con Google: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return redirect('/login')->with('error', 'No se pudo iniciar sesión con Google. Inténtalo de nuevo. Error: ' . $e->getMessage());
    }
});

// Ruta de prueba de rendimiento (sin lógica compleja)
Route::get('/test-perf', function () {
    Log::info('PerfTest: /test-perf started');
    $response = 'Hello from simple route!';
    Log::info('PerfTest: /test-perf ended');
    return $response;
});


// Rutas de autenticación (solo para invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Vista del login exclusivo para veterinarios
    Route::get('/veterinario/login', fn() => view('login'))->name('veterinarian.login'); // Puede usar la misma vista de login si se maneja la lógica en el controller
    Route::post('/veterinario/login', [AuthController::class, 'login'])->name('veterinarian.login.submit');
});

// Incluye las rutas de autenticación de Laravel (registro, reseteo de contraseña, verificación de email)
require __DIR__ . '/auth.php';


// Dashboard genérico después de iniciar sesión (para clientes)

//fabricio: voy a comentar esto para solucionar el problema de variable indefinida de recentPost, pegare una abajo
//de la misma como solucion, pero dejo por si causa problemas.

// Route::get('/dashboard', function () {
//     return view('client.welcome');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/dashboard', function () {
    return redirect()->route('client.home');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/admin/orders/export-pdf', [OrderExportController::class, 'exportPdf'])
            ->name('admin.orders.export-pdf');
        // --- FIN DE LA RUTA DE EXPORTACIÓN ---

Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas de perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update-personal', [ProfileController::class, 'updatePersonal'])->name('profile.update-personal');
    Route::patch('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/CientsOrders', [ClientOrderController::class, 'index'])->name('ClientOrders.index'); // <-- Esto parece más un historial de órdenes de cliente


    // --- RUTA PARA EXPORTACIÓN DE ÓRDENES DE FILAMENT A PDF ---
        // Esta ruta debe estar dentro del middleware 'auth' y 'verified'.
        // La lógica de autorización por rol ('Administrador') se encuentra en el controlador.
        

    Route::prefix('notifications')->name('notifications.')->group(function () {

        Route::get('/', [NotificationController::class, 'index'])->name('index'); // URL: /notifications, Nombre: notifications.index
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show'); // URL: /notifications/{notification}, Nombre: notifications.show

        // RUTA MARCADA COMO LEÍDA (CORREGIDA)
        // Antes: Route::post('/mark-as-read/{notification}', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        // Esto crearía notifications.markAsRead (con "A" mayúscula)
        // Si tu Blade usa 'mark-as-read', entonces necesitas que el nombre sea 'mark-as-read'
        Route::put('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read'); // URL: /notifications/{notification}/mark-as-read, Nombre: notifications.mark-as-read

        // RUTA MARCAR TODAS COMO LEÍDAS (CORREGIDA)
        // Asegúrate de que el nombre coincida con lo que uses en tu Blade
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read'); // URL: /notifications/mark-all-as-read, Nombre: notifications.mark-all-as-read

        // **LA LÍNEA CLAVE A CORREGIR PARA EL MÉTODO 'aceptarReprogramacion'**
        Route::post('/reprogramacion/{reprogrammingRequest}/aceptar', [NotificationController::class, 'aceptarReprogramacion'])
            ->name('reprogramacion.aceptar');

        Route::delete('/clear-read', [NotificationController::class, 'clearRead'])->name('clear.read'); // URL: /notifications/clear-read, Nombre: notifications.clear.read
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy'); // URL: /notifications/{notification}, Nombre: notifications.destroy

    }); // Cierre del grupo de notificaciones



    // Rutas de gestión de mascotas para el cliente
    Route::prefix('mi-mascota')->name('client.mascotas.')->group(function () {
        Route::get('/', [MascotaController::class, 'index'])->name('index');
        Route::get('/registrar', [MascotaController::class, 'create'])->name('create');
        Route::post('/', [MascotaController::class, 'store'])->name('store');
        Route::get('/{mascota}', [MascotaController::class, 'show'])->name('show');
        Route::get('/{mascota}/editar', [MascotaController::class, 'edit'])->name('edit');
        Route::put('/{mascota}', [MascotaController::class, 'update'])->name('update');
        Route::delete('/{mascota}', [MascotaController::class, 'destroy'])->name('destroy');
    });
    Route::post('veterinarians/working-days', [App\Http\Controllers\Client\CitaController::class, 'getVeterinarianWorkingDays'])->name('client.veterinarians.working-days');
    // Rutas de gestión de citas para el cliente
    Route::prefix('/citas')->name('client.citas.')->group(function () {
        Route::get('/', [CitaController::class, 'index'])->name('index');
        Route::get('/agendar', [CitaController::class, 'create'])->name('create');
        Route::post('/', [CitaController::class, 'store'])->name('store');
        Route::get('/complete-booking', [CitaController::class, 'completeBookingAfterPayment'])->name('complete_booking');
        Route::get('/get-veterinarians-by-service', [CitaController::class, 'getVeterinariansByService'])->name('get-veterinarians-by-service');
        Route::get('/available-slots', [CitaController::class, 'getAvailableTimeSlots'])->name('get-available-slots');
        Route::get('/{appointment}', [CitaController::class, 'show'])->name('show');

        // Rutas para Reprogramación de Citas

        // Vista del formulario de reprogramación
        Route::get('/{appointment}/reprogramar', [CitaController::class, 'edit'])->name('edit');

        // Procesa la reprogramación (puede ser PATCH o PUT)
        Route::put('/{appointment}', [CitaController::class, 'update'])->name('update');

        Route::get('/solicitudes-reprogramacion/{reprogrammingRequest}', [\App\Http\Controllers\Client\CitaController::class, 'showReprogrammingRequest'])->name('reprogramming_requests.show');
        Route::post('/solicitudes-reprogramacion/{reprogrammingRequest}/aceptar', [\App\Http\Controllers\Client\CitaController::class, 'acceptReprogrammingRequest'])->name('reprogramming_requests.accept');
        Route::post('/solicitudes-reprogramacion/{reprogrammingRequest}/rechazar', [\App\Http\Controllers\Client\CitaController::class, 'rejectReprogrammingRequest'])->name('reprogramming_requests.reject');

        // Route::get('/{appointment}/reprogramar', [App\Http\Controllers\Client\CitaController::class, 'showReprogrammingForm'])->name('reprogram.form');
        // Route::post('/{appointment}/reprogramar/solicitar', [App\Http\Controllers\Client\CitaController::class, 'storeReprogrammingRequest'])->name('reprogram.store');
        // Route::get('/{appointment}/reprogramar/estado', [App\Http\Controllers\Client\CitaController::class, 'showReprogrammingStatus'])->name('reprogram.status');

        // // Nueva ruta para la respuesta del cliente (aceptar o contraproponer)
        // Route::post('/{appointment}/reprogramar/responder', [App\Http\Controllers\Client\CitaController::class, 'respondToReprogrammingRequest'])->name('reprogram.respond');

        // // Ruta para retirar una propuesta iniciada por el cliente (usa la ReprogrammingRequest ID)
        // Route::post('/reprogramar/{reprogrammingRequest}/retirar', [App\Http\Controllers\Client\CitaController::class, 'retractClientProposal'])->name('reprogram.retract_proposal');

        // // Ruta para Cancelar Cita Definitivamente
        // Route::post('/{appointment}/cancelar', [App\Http\Controllers\Client\CitaController::class, 'cancelAppointment'])->name('cancel');

    });



    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/checkout/{serviceOrderId}', [PaymentController::class, 'showCheckoutPage'])->name('show_checkout_page');
        Route::post('/purchase-service', [PaymentController::class, 'purchaseService'])->name('purchase_service');
        Route::post('/create-order', [PaymentController::class, 'checkout'])->name('checkout');

        //nota: Solo agreguéw /{serviceOrderId fea
        Route::get('/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('success'); // <-- NOTA: El name es 'success' aquí, con el prefix 'payments.' se convierte en 'payments.success'
        Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success'); // Callback de PayPal
        // Route::get('/payments/success/{serviceOrderId}', [PaymentController::class, 'success'])->name('payments.success'); // Callback de PayPal


        Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel'); // Callback de PayPal
        Route::get('/transaction-confirmed/{service_order_id}', [PaymentController::class, 'showCompletedTransactionView'])->name('transaction_confirmed_view');
        Route::view('/failed', 'client.checkout.failed')->name('failed');
        Route::view('/error-page', 'client.checkout.error')->name('error_page');
    });

    // --- RUTAS PARA DESCARGA DE PDFs DE CITAS Y RECIBOS DE PAGO (LAS QUE NECESITAS REEMPLAZAR/AÑADIR) ---
    // Estas rutas son manejadas por el PaymentController y son las que estabas preguntando.
    // Aseguran que el PDF se descargue si el usuario está autenticado y es el dueño del documento.
    Route::get('/download/appointment/{appointmentId}/invoice', [PaymentController::class, 'downloadAppointmentInvoice'])->name('download.appointment_invoice');
    Route::get('/download/payment/{serviceOrderId}/receipt', [PaymentController::class, 'downloadPaymentReceipt'])->name('download.payment_receipt');
    // --- FIN DE RUTAS DE DESCARGA DE PDFs ---



    Route::prefix('cart-payments')->name('cart_payments.')->group(function () {
        Route::post('/pay', [ProductPaymentController::class, 'payWithPaypal'])->name('pay');
        Route::get('/success', [ProductPaymentController::class, 'paypalSuccess'])->name('success');
        Route::get('/cancel', [ProductPaymentController::class, 'paypalCancel'])->name('cancel');
        Route::get('/order-details/{order}', [ProductPaymentController::class, 'showProductOrderConfirmedView'])->name('order_details');
    });

    // Rutas para la descarga del comprobante de productos (ya estaba en tu código, la mantengo)
    Route::get('/download-invoice/product/{order}', function (Order $order) {
        if (Auth::id() !== $order->user_id && (!Auth::check() || !Auth::user()->is_admin)) {
            abort(403, 'No tienes permiso para descargar este comprobante.');
        }

        $filePath = storage_path('app/public/invoices/products/comprobante_producto_' . $order->id . '.pdf');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'comprobante_producto_' . $order->id . '.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } else {
            Log::warning("Comprobante PDF no encontrado para la orden ID: {$order->id} en la ruta: {$filePath}");
            abort(404, 'Comprobante no encontrado.');
        }
    })->name('download.product_invoice');
}); // Fin del grupo de middleware 'auth' y 'verified'


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



// // Rutas de autenticación (solo para invitados)
// Route::middleware('guest')->group(function () {
//     Route::get('/login', fn() => view('login'))->name('login');
//     Route::post('/login', [AuthController::class, 'login']);
// }); //ruta duplicada 
//datima, Fatima ,  porque dice cuando el usuario es diferente a veterinario redirigir a veterinarian.login, no deberia ser igual?| qye yo no lo puseeeeee solo digoooo y en role está con V grande no con v por eso no lo dejaba entrar.. la logica está mal, lo deja entrar así porqu eno tenemos ninguna role con 
//"veterinario" sino "Veterinario",    pero dice que con V no lo dejaba entrar, en todo caso modificar desde admin su rol, otra cosa, no es un enum? | si, por eso, lo deja entrar así porque no tneemos ningurole veterinario pues, el admin entra a admin, el Ciente etra a cliente
//y el veterinario debe entrar a Veterinario pero como no hay role veterinario, si lo dirije,  seria un == Veterinario


// Rutas protegidas (solo para usuarios autenticados)

Route::middleware('auth')->group(function () {

    // 1. Ruta para MOSTRAR la información del perfil (tu info.blade.php)
    // Cuando el usuario va a "Mi Información" en el sidebar
    Route::get('/veterinario/perfil', [VeterinarianController::class, 'showProfile'])->name('veterinarian.profile');

    // 2. Ruta para MOSTRAR el FORMULARIO DE EDICIÓN (tu index.blade.php)
    // Cuando el usuario hace clic en "Editar Perfil" desde info.blade.php
    Route::get('/veterinario/perfil/editar', [VeterinarianController::class, 'index'])->name('veterinarian.edit.my'); // <-- Cambio crítico aquí. Apunta a tu método 'index'

    // 3. Ruta para GUARDAR los cambios del perfil (acción del formulario en index.blade.php)
    Route::patch('/veterinario/perfil', [VeterinarianController::class, 'update'])->name('veterinarian.profile.update');

    Route::get('/veterinario/specialties', [VeterinarianController::class, 'searchSpecialties'])->name('veterinarian.specialties.search');

    // Si también usas /index para el dashboard principal del veterinario (no para edición):
    Route::get('/index', [VeterinarianController::class, 'index'])->name('index'); // <-- Cambiado 'dashboard' a 'index'
    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Route::get('/perfil/editar', [VeterinarianController::class, 'editMyProfile'])->name('veterinarian.edit.my');

    // Route::get('/perfil/editar/{id}', [VeterinarianController::class, 'edit'])->name('veterinarian.edit');
    // Route::patch('/veterinario/perfil', [VeterinarianController::class, 'update'])->name('veterinarian.profile.update');

    // Citas
    Route::get('/citas-agendadas', [CitaController::class, 'citasAgendadas'])->name('veterinarian.citas');
    Route::get('/citasagendadas/cliente/{id}/cita/{cita}', [CitaController::class, 'verMascotas'])->name('ver.mascotas');
    Route::get('/cita/{id}/atender', [VeterinarianController::class, 'formularioAtencion'])->name('veterinarian.atender');
    Route::post('/guardar-atencion', [VeterinarianController::class, 'guardarAtencion'])->name('veterinarian.guardar.atencion');
    Route::post('/cancelar-cita', [VeterinarianController::class, 'cancelarCita'])->name('veterinarian.cancelar.cita');
    Route::post('/veterinarian/reprogramar-cita', [VeterinarianController::class, 'reprogramarCita'])->name('veterinarian.reprogramar.cita');

    // ¡NUEVA RUTA AGREGADA AQUÍ para obtener horarios disponibles!
    Route::get('/veterinario/get-available-schedules/{appointmentId}', [VeterinarianController::class, 'getAvailableSchedules'])->name('veterinarian.get.available.schedules');

    // <-- NUEVA RUTA AGREGADA AQUÍ
    // Rutas para las acciones de notificaciones de reprogramación
    Route::post('/veterinario/reprogramacion/aceptar', [VeterinarianController::class, 'aceptarReprogramacion'])
        ->name('veterinarian.reprogramacion.aceptar');
    // <-- NUEVA RUTA AGREGADA AQUÍ
    Route::post('/veterinario/reprogramacion/retirar-propuesta', [VeterinarianController::class, 'retirarPropuestaReprogramacion'])
        ->name('veterinarian.reprogramacion.retirar_propuesta');


    // ¡NUEVA RUTA AGREGADA AQUÍ para rechazar! (Si decides usar el método que te pasé)
    Route::post('/veterinario/reprogramacion/rechazar', [VeterinarianController::class, 'rechazarReprogramacion'])
        ->name('veterinarian.reprogramacion.rechazar');

    Route::post('/veterinarian/confirmar-cita', [VeterinarianController::class, 'confirmarCita'])->name('veterinarian.confirmar.cita');


    // Historial médico (desde controlador dedicado)
    Route::get('/historiales', [HistorialMedicoController::class, 'index'])->name('historialmedico.index');
    Route::get('/historiales/{id}', [HistorialMedicoController::class, 'verHistorial'])->name('historialmedico.show');

    // NUEVA RUTA: Historial médico por mascota (desde controlador VeterinarianController)
    Route::get('/veterinarian/historial/{mascota}', [VeterinarianController::class, 'verHistorial'])->name('veterinarian.historial');

    Route::get('/datosestadisticos', [VeterinarianController::class, 'datosEstadisticos'])->name('datosestadisticos');

    Route::get('/veterinario/notificaciones', [VeterinarianController::class, 'notificaciones'])
        ->name('veterinarian.notificaciones');


    // ¡NUEVAS RUTAS PARA EXPORTAR!
    // Ruta para exportar el historial completo de una mascota
    Route::get('/historial/{mascota}/export/completo', [HistorialMedicoController::class, 'exportHistorialCompleto'])->name('veterinarian.historial.exportCompleto');

    // Ruta para exportar una sola cita (MedicalRecord)
    // Usamos 'registro' como parámetro para el Route Model Binding con MedicalRecord
    Route::get('/historial/cita/{registro}/export', [HistorialMedicoController::class, 'exportCita'])->name('veterinarian.historial.exportCita');
});
