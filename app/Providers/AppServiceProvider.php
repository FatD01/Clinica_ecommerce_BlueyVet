<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use App\Models\User; // Asegúrate de que User exista y esté importado si lo usas
use App\Observers\UserObserver; // Asegúrate de que UserObserver exista y esté importado si lo usas
use Illuminate\Support\Facades\View; // Asegúrate de que View exista y esté importado si lo usas
use App\Http\View\Composers\CartComposer; // Asegúrate de que CartComposer exista y esté importado si lo usas
use Illuminate\Support\Facades\Config;
use Carbon\Carbon; // ¡Importante: para trabajar con fechas y obtener el mes/año actual!
use App\Models\Product; // Importa el modelo Product
use App\Observers\ProductObserver; // Importa el observer ProductObserver

// === NUEVOS IMPORTS PARA NOTIFICACIONES ===
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void

    {
        User::observe(UserObserver::class);


        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }


        Product::observe(ProductObserver::class);

        // === INICIO: COMPARTIR VARIABLE DE NOTIFICACIONES ===
        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->role === 'veterinario' && Auth::user()->veterinarian) {
                $vetId = Auth::user()->veterinarian->id;
                $tomorrow = Carbon::tomorrow()->toDateString();

                $hasCitas = Appointment::where('veterinarian_id', $vetId)
                    ->whereDate('date', $tomorrow)
                    ->exists();

                $notificacionesVistas = session('notificaciones_vistas.' . $tomorrow, false);

                $view->with('unreadCount', $hasCitas && !$notificacionesVistas);
            } else {
                $view->with('unreadCount', false);
            }
        });
        // === FIN: COMPARTIR VARIABLE DE NOTIFICACIONES ===

        // --- INICIO: CÓDIGO DE PRUEBA TEMPORAL PARA EL SCHEDULER ---
        // Este bloque se ejecuta cuando Laravel ha terminado de cargar sus servicios.
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Obtener el correo del destinatario del reporte desde MAIL_FROM_ADDRESS en el .env
            // Laravel ya carga MAIL_FROM_ADDRESS en su configuración de 'mail.from.address'
            $reportRecipientEmail = config('mail.from.address');

            // Pequeña validación para asegurar que el correo es válido antes de programar
            if (empty($reportRecipientEmail) || !filter_var($reportRecipientEmail, FILTER_VALIDATE_EMAIL)) {
                Log::error("La variable 'MAIL_FROM_ADDRESS' en .env no está configurada o no contiene una dirección de correo válida. Reporte mensual de PRUEBA no programado.");
                return; // Si el correo no es válido, no programamos la tarea
            }

            // --- LÍNEA DE PRODUCCIÓN (COMENTADA PARA LA PRUEBA) ---
            // Esta es la línea que usarías normalmente para el envío mensual.
            // La comentamos para que no interfiera con nuestra prueba de ejecución inmediata.
            /*
            $schedule->command("report:monthly-appointments --email={$reportRecipientEmail}")
                     ->monthlyOn(1, '03:00') // El día 1 de cada mes a las 3 AM
                     ->timezone('America/Lima') // Configura tu zona horaria correcta aquí (Trujillo es America/Lima)
                     ->onSuccess(function () {
                         Log::info('Reporte mensual de citas enviado exitosamente.');
                     })
                     ->onFailure(function () {
                         Log::error('Fallo al enviar el reporte mensual de citas.');
                     });
            */
            // --- FIN LÍNEA DE PRODUCCIÓN (COMENTADA) ---


            // --- LÍNEA DE PRUEBA (DESCOMENTADA DURANTE LA PRUEBA) ---
            // Esta línea programará el comando para ejecutarse cada minuto.
            // Obtener el mes y año actual para pasar al comando de reporte.
            $currentMonth = Carbon::now('America/Lima')->month; // Mes actual (ej. 6 para Junio)
            $currentYear = Carbon::now('America/Lima')->year;   // Año actual (ej. 2025)

            // El comando se ejecutará cada minuto, enviando un reporte para el mes y año actual.
            $schedule->command("report:monthly-appointments --email={$reportRecipientEmail} --month={$currentMonth} --year={$currentYear}")
                ->everyMinute() // ¡Esta es la clave para la prueba rápida!
                ->onSuccess(function () {
                    Log::info('[PRUEBA RÁPIDA] Reporte de citas para ' . Carbon::now('America/Lima')->format('F Y') . ' enviado exitosamente.');
                })
                ->onFailure(function () {
                    Log::error('[PRUEBA RÁPIDA] Fallo al enviar el reporte de citas para ' . Carbon::now('America/Lima')->format('F Y') . '.');
                });
            // --- FIN LÍNEA DE PRUEBA ---

        });
        // --- FIN: CÓDIGO DE PRUEBA TEMPORAL ---
    }
}
