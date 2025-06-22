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

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;


use App\Filament\Widgets\AppointmentsChart;
// use App\Filament\Pages\Dashboard; // Commented out because Dashboard does not exist or is undefined

class DashboardPanelProvider extends PanelProvider // ¡Cambia esto de 'Panel' a 'PanelProvider'!
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
            // ->notifications()
            ->plugins([
                // FilamentFullCalendarPlugin::make(),
            ]);
    }
}