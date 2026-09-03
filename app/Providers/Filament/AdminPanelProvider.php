<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Register;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration(config('app.allow_registration') ? Register::class : null)
            ->brandName('MOT')
            ->topNavigation(true)
            // ponytail: Filament has no native top-right header actions on mobile (only Adaptive/Bottom); force row layout in widget tables
            // ponytail: Filament has no native FAB — quick-create menu via render hook; ?action=create is native action mounting
            ->renderHook('panels::body.end', fn (): string => view('filament.quick-create')->render())
            ->renderHook('panels::head.end', fn (): string => '
                <link rel="manifest" href="/manifest.webmanifest">
                <meta name="theme-color" content="#f59e0b">
                <link rel="apple-touch-icon" href="/icons/icon-192.png">
                <meta name="mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="default">
                <script>navigator.serviceWorker?.register("/sw.js")</script>')
            ->renderHook('panels::body.start', fn (): string => '<style>@media (max-width: 639px) { .fi-wi-table .fi-ta-header-adaptive-actions-position { flex-direction: row; align-items: center; flex-wrap: wrap; } .fi-wi-table .fi-ta-header-adaptive-actions-position .fi-ta-actions { margin-inline-start: auto; } }</style>')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
