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
            ->favicon(fn (): string => BrandSetting::logoUrl())
            ->brandName(fn (): string => BrandSetting::current()?->name ?? 'DaImperium')
            ->brandLogo(fn (): string => BrandSetting::logoUrl())
            ->brandLogoHeight('2.5rem')
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn (): string => __('Profile'))
                    ->url(fn (): string => route('profile')),
            ])
            ->renderHook('tables::toolbar.start', fn () => view('filament.mobile-table-toggle'))
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.panel-switcher'))
            ->colors([
                'primary' => Color::Sky,
                'warning' => Color::Orange,
            ])
            ->renderHook(
                PanelsRenderHook::STYLES_BEFORE,
                fn (): string => sprintf(
                    '<style>:root{--brand-accent:%s;--brand-excel:%s}.fi-header{border-top:2px solid var(--brand-accent)!important;border-radius:.75rem;padding-top:1rem}.fi-btn-color-warning{background-color:var(--brand-excel)}</style>',
                    BrandSetting::accentColor(),
                    BrandSetting::excelColor(),
                )
            )
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

                        .dark .fi-body { background: #0b1220; }
                        .dark :is(.fi-section-header, .fi-ta-header, .fi-modal-header, .fi-wi-stats-overview-stat-description) { background-color: #1e293b; border-color: #334155; }
                        .dark :is(.fi-section-content, .fi-ta-content, .fi-modal-content) { background-color: #111827; }
                        .dark :is(.fi-section, .fi-ta-ctn, .fi-fo-field-wrp > div) { border-color: #334155; }
                        .dark .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-btn:hover { background: #1e293b; }
                        .app-admin-table-toggle { display: none; }
                        @media (max-width: 767px) {
                            .app-admin-table-toggle { display: inline-flex; align-items: center; border: 1px solid #94a3b8; border-radius: .5rem; padding: .25rem .5rem; font-size: .75rem; }
                            .fi-ta-ctn:not(.app-table-full) [data-mobile-secondary=true] { display: none; }
                            .fi-ta-ctn:not(.app-table-full) .fi-ta-text-item { white-space: normal; overflow-wrap: anywhere; }
                            .fi-main { padding-inline: .75rem; }
                            .fi-header { padding-top: .5rem; gap: .5rem; }
                            .fi-header-heading { font-size: 1.25rem; }
                            .fi-header-actions { gap: .375rem; }
                            .fi-ta-cell, .fi-ta-header-cell { padding-inline: .5rem; }
                            .fi-ta-ctn:not(.app-table-full) .fi-ta-col { padding: .5rem .25rem; }
                            .fi-ta-selection-cell { width: 2rem; }
                            .fi-ta-ctn:not(.app-table-full) .fi-ta-cell-name { min-width: 8rem; }
                            .fi-section-header, .fi-section-content, .fi-modal-header, .fi-modal-content, .fi-modal-footer { padding: .75rem; }
                        }
                        /* Keep Filament's responsive layouts and enlarge touch controls. */
                        @media (max-width: 639px), (pointer: coarse) {
                            .fi-body :is(button:not([role="switch"]), .fi-btn, .fi-sidebar-item-btn, .fi-pagination-item-btn) {
                                min-height: 44px;
                            }
                            .fi-body button:not([role="switch"]) { min-width: 44px; }
                            .fi-body :is(.fi-input, .fi-select-input) {
                                min-height: 44px;
                                font-size: 16px;
                            }
                            .fi-main { min-width: 0; }
                        }
                    </style>
                    HTML
            )
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => <<<'HTML'
                    <script>
                        window.addEventListener('brand-settings-updated', () => window.location.reload());
                    </script>
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
