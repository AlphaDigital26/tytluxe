<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('tyt-console')
            ->login()
            ->authGuard('admin')
            ->colors([
                'primary' => Color::hex('#c9a84c'),
                'danger' => Color::Rose,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->brandName('TYT Luxe')
            ->favicon(asset('assets/images/favicon.png'))
            ->font('Jost')
            ->sidebarWidth('15rem')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make()
                     ->label('Operations')
                     ->icon('heroicon-o-briefcase'),
                \Filament\Navigation\NavigationGroup::make()
                     ->label('Cruise Catalog'),
                \Filament\Navigation\NavigationGroup::make()
                     ->label('System')
                     ->icon('heroicon-o-cog-6-tooth')
                     ->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\DashboardStatsOverview::class,
                \App\Filament\Widgets\EnquiriesByCategoryChart::class,
                \App\Filament\Widgets\EnquiriesOverTimeChart::class,
                \App\Filament\Widgets\LatestEnquiriesWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render(<<<'HTML'
                    <style>
                        /* Hide scrollbar from sidebar */
                        aside.fi-sidebar nav, 
                        .fi-sidebar-nav, 
                        .fi-sidebar-nav-groups,
                        aside.fi-sidebar > div,
                        aside.fi-sidebar main {
                            scrollbar-width: none !important;
                            -ms-overflow-style: none !important;
                        }
                        aside.fi-sidebar nav::-webkit-scrollbar,
                        .fi-sidebar-nav::-webkit-scrollbar,
                        .fi-sidebar-nav-groups::-webkit-scrollbar,
                        aside.fi-sidebar > div::-webkit-scrollbar,
                        aside.fi-sidebar main::-webkit-scrollbar {
                            display: none !important;
                        }

                        /* Add vertical divider between sidebar and main content */
                        .fi-sidebar {
                            border-right: 1px solid #d1d5db !important; /* gray-300 */
                            box-shadow: 1px 0 0 0 rgba(0,0,0,0.05) !important;
                        }
                        .dark .fi-sidebar {
                            border-right: 1px solid #374151 !important; /* gray-700 */
                            box-shadow: 1px 0 0 0 rgba(255,255,255,0.05) !important;
                        }
                    </style>
                HTML)
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render(<<<'HTML'
                    <script>
                        document.addEventListener('livewire:init', () => {
                            Livewire.hook('request.error', ({ status, preventDefault }) => {
                                if (status === 419) {
                                    preventDefault();
                                    new FilamentNotification()
                                        .title('Session Expired')
                                        .body('Your session has expired (likely due to background development updates). Please refresh the page.')
                                        .warning()
                                        .send();
                                }
                            });
                        });
                    </script>
                HTML)
            );
    }
}
