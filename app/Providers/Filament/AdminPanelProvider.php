<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetUserLocale;
use App\Models\BrandSetting;
use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->login(Login::class)
            ->favicon(asset('favicon.svg') . '?v=3')
            ->brandName(fn (): string => BrandSetting::current()?->name ?? 'DaImperium')
            ->brandLogo(fn (): string => BrandSetting::logoUrl())
            ->brandLogoHeight('2.5rem')
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn (): string => __('Profile'))
                    ->url(fn (): string => route('profile')),
            ])
            ->colors([
                'primary' => Color::Sky,
                'warning' => Color::Orange,
            ])
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => <<<'HTML'
                    <style>
                        .fi-sidebar {
                            border-right: 1px solid #bae6fd;
                            background: #f0f9ff;
                            box-shadow: 6px 0 18px rgba(3, 105, 161, 0.08);
                        }

                        .fi-sidebar-header,
                        .fi-sidebar-nav,
                        .fi-sidebar-footer {
                            background: #f0f9ff;
                        }

                        .fi-sidebar-group + .fi-sidebar-group {
                            margin-top: 0.75rem;
                            padding-top: 0.75rem;
                            border-top: 1px solid #bae6fd;
                        }

                        .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
                            border: 1px solid #fdba74;
                            background: #fff7ed;
                            color: #075985;
                            box-shadow: inset 3px 0 0 #fb923c;
                        }

                        .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-btn:hover {
                            background: #e0f2fe;
                        }

                        .fi-body {
                            background: #f1f5f9;
                        }

                        .fi-section,
                        .fi-ta-ctn,
                        .fi-fo-field-wrp > div {
                            border-color: #bae6fd;
                        }

                        .fi-section-header,
                        .fi-ta-header,
                        .fi-modal-header,
                        .fi-wi-stats-overview-stat-description {
                            border-color: #e3edf4;
                            background-color: #f6f9fb;
                        }

                        .fi-section-content,
                        .fi-ta-content,
                        .fi-modal-content {
                            background-color: #ffffff;
                        }

                        .dark .fi-sidebar,
                        .dark .fi-sidebar-header,
                        .dark .fi-sidebar-nav,
                        .dark .fi-sidebar-footer {
                            background: #111827;
                        }

                        .dark .fi-sidebar {
                            border-right-color: #334155;
                            box-shadow: 6px 0 18px rgba(0, 0, 0, 0.22);
                        }

                        .dark .fi-sidebar-group + .fi-sidebar-group {
                            border-top-color: #334155;
                        }

                        .dark .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
                            border-color: #1d4ed8;
                            background: #1e3a8a;
                            color: #dbeafe;
                        }
                    </style>
                    HTML
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                SetUserLocale::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
