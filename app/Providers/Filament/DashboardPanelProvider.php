<?php

namespace App\Providers\Filament;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Session\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider; // ¡Asegúrate de que esta línea esté presente y NO comentada!
use Filament\Widgets;
use Filament\Widgets\AccountWidget;
use App\Models\User;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Auth;

use App\Filament\Widgets\AppointmentsChart;
// use App\Filament\Pages\Dashboard; // Commented out because Dashboard does not exist or is undefined
use Illuminate\Support\Facades\Log;
class DashboardPanelProvider extends PanelProvider // ¡Cambia esto de 'Panel' a 'PanelProvider'!
{
    public function panel(Panel $panel): Panel
    {
        // dd('PanelProvider initializing. Current User:', Auth::user()?->email, 'Role:', Auth::user()?->role);

         // DD DE PRUEBA: ¿Se ejecuta este PanelProvider al acceder a /admin?
        // Esto solo se ejecutará si Laravel registra y "arranca" este panel.
        // Si no ves esto, es que Laravel no está llamando a este PanelProvider
        // para la ruta /admin.
        // dd('Filament Dashboard Panel Provider is being booted.');
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login() 
            ->colors([
                'primary' => '#FFC107', // amber color in hex
            ])
     
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // \App\Filament\Widgets\VeterinarianCalendarWidget::class,
                AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                AppointmentsChart::class,
                
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,

                //esto puse yo
                DispatchServingFilamentEvent::class,
            ])
            
            ->authMiddleware([
                Authenticate::class,
                'admin',
            ])
            
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
            // ->notifications()
            ->plugins([
                
            ]);
    }
}