<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\CitaController;
use App\Http\Controllers\Client\ServicioController; // Importa tu ServicioController

Route::get('/client/home', [HomeController::class, 'index'])->name('client.home');

Route::get('/', function () {
    return view('client.welcome');
});

Route::prefix('/citas')->name('client.citas.')->group(function () {
    Route::get('/', [CitaController::class, 'index'])->name('index');
    // Si tienes una ruta POST para agendar citas reales, iría aquí, por ejemplo:
    // Route::post('/', [CitaController::class, 'store'])->name('store');
});

Route::prefix('/servicios')->name('client.servicios.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Client\ServicioController::class, 'index'])->name('index');

    // ¡NUEVA RUTA para manejar el envío del formulario de contacto por correo!
    // Usamos 'contact-mail' como nombre para esta acción específica
    Route::post('/send-contact-mail', [ServicioController::class, 'sendContactMail'])->name('send-contact-mail');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';