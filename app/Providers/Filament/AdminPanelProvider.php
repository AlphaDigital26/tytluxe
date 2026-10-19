<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
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
    public function boot(): void
    {
        // Number boxes inside tabs (most admin forms) get no HTML "min" from
        // Filament, so the browser's ▼ button runs into negatives. Hand the
        // lower limit over as data-min for the script below instead — a real
        // "min" on a box in a hidden tab would make the browser silently
        // refuse to save.
        \Filament\Forms\Components\TextInput::configureUsing(function (\Filament\Forms\Components\TextInput $input): void {
            $input->extraInputAttributes(
                fn (\Filament\Forms\Components\TextInput $component): array => $component->isNumeric() && filled($min = $component->getMinValue())
                    ? ['data-min' => $min]
                    : [],
                merge: true,
            );
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('tyt-console')
            ->login()
            // "Forgot password?" link on the login page; the reset link is
            // emailed to the staff member (see passwords.admins in config/auth.php).
            ->passwordReset()
            ->authGuard('admin')
            ->authPasswordBroker('admins')
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
                \Filament\Navigation\NavigationGroup::make()->label('Bookings & Leads'),
                \Filament\Navigation\NavigationGroup::make()->label('Hotels'),
                \Filament\Navigation\NavigationGroup::make()->label('Flights'),
                \Filament\Navigation\NavigationGroup::make()->label('Packages & Offers'),
                \Filament\Navigation\NavigationGroup::make()->label('Cruises'),
                \Filament\Navigation\NavigationGroup::make()->label('Website Content'),
                \Filament\Navigation\NavigationGroup::make()->label('Travel Journal')->collapsed(),
                \Filament\Navigation\NavigationGroup::make()->label('Customers & Team')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // Home screen, top to bottom (each widget's $sort sets the order).
            ->widgets([
                \App\Filament\Widgets\WelcomeBanner::class,
                \App\Filament\Widgets\TodoList::class,
                \App\Filament\Widgets\BusinessSnapshot::class,
                \App\Filament\Widgets\HotelsOverview::class,
                \App\Filament\Widgets\FlightsOverview::class,
                \App\Filament\Widgets\PackagesOverview::class,
                \App\Filament\Widgets\SalesChart::class,
                \App\Filament\Widgets\UpcomingTrips::class,
                \App\Filament\Widgets\EnquiriesByCategoryChart::class,
                \App\Filament\Widgets\LatestEnquiriesWidget::class,
                \App\Filament\Widgets\DashboardStatsOverview::class,
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

                        // Number boxes: the mouse wheel must scroll the page, not
                        // change the value (drop focus so the browser leaves it be),
                        // and a box with a lower limit (min) can't go below it —
                        // no "-" key, and a lower value is raised to the limit.
                        (function () {
                            function minOf(el) {
                                if (! el || el.type !== 'number') return null;
                                var min = el.dataset.min !== undefined ? el.dataset.min : el.min;
                                return min !== '' && min !== undefined ? parseFloat(min) : null;
                            }
                            document.addEventListener('wheel', function (e) {
                                var el = document.activeElement;
                                if (el && el.type === 'number' && el === e.target) el.blur();
                            }, { capture: true, passive: true });
                            document.addEventListener('keydown', function (e) {
                                var min = minOf(e.target);
                                if (min !== null && min >= 0 && e.key === '-') e.preventDefault();
                            }, true);
                            // Capture phase: runs before Livewire reads the value.
                            // The ▼ button / arrow keys (no inputType) stop at the limit
                            // straight away. While typing only negatives are stopped
                            // (typing "0" on the way to "0.5" is fine); on leaving the
                            // box, the full limit.
                            ['input', 'change'].forEach(function (type) {
                                document.addEventListener(type, function (e) {
                                    var el = e.target, min = minOf(el);
                                    if (min === null || el.value === '') return;
                                    var v = parseFloat(el.value), typing = type === 'input' && !! e.inputType;
                                    if (! typing && v < min) el.value = min;
                                    else if (typing && min >= 0 && v < 0) el.value = 0;
                                }, true);
                            });
                        })();
                    </script>
                HTML)
            );
    }
}
