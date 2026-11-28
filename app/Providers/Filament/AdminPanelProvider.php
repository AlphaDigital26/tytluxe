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

        // Error messages name the box as it reads on screen, in quotes:
        // 'The "Web address" field is required.' Filament's default lower-
        // cases the first letter ("The uRL Slug (auto-filled…) field…").
        \Filament\Forms\Components\Field::configureUsing(function (\Filament\Forms\Components\Field $field): void {
            $field->validationAttribute(fn (\Filament\Forms\Components\Field $component): string => self::plainLabel($component));
        });

        // One date style everywhere: 17 Sep 2026 / 17 Sep 2026, 7:18 AM.
        foreach ([\Filament\Tables\Table::class, \Filament\Schemas\Schema::class] as $class) {
            $class::configureUsing(fn ($component) => $component
                ->defaultDateDisplayFormat('j M Y')
                ->defaultDateTimeDisplayFormat('j M Y, g:i A')
                ->defaultTimeDisplayFormat('g:i A'));
        }
    }

    /** "URL Slug (auto-filled from title)" → "URL Slug"; "Which tab?" → "Which tab". */
    public static function plainLabel(\Filament\Forms\Components\Field $component): string
    {
        $label = $component->getLabel();
        $label = $label instanceof \Illuminate\Contracts\Support\Htmlable || blank($label) ? $component->getDefaultLabel() : (string) $label;
        $label = trim(preg_replace('/\s*\([^)]*\)/', '', strip_tags($label)));

        return '"'.rtrim($label, ' ?:').'"';
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('tyt-console')
            // A save either stores everything or nothing: a package that failed
            // half-way used to stay behind without its departures.
            ->databaseTransactions()
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
                \Filament\Navigation\NavigationGroup::make()->label('Cabs'),
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
                \App\Filament\Widgets\CabsOverview::class,
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
                // The panel doesn't use the "web" group, so add the site's
                // headers here: above all, no other site may frame the admin.
                \App\Http\Middleware\SecurityHeaders::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render(<<<'HTML'
                    <style>
                        /* Chart period menu (e.g. "Last 30 days") stays on the right of the title. */
                        .fi-wi-chart .fi-section-header-content { display: flex; flex-wrap: nowrap; align-items: flex-start; gap: 16px; }
                        .fi-wi-chart .fi-section-header-text-ctn { flex: 1 1 auto; min-width: 0; }
                        .fi-wi-chart .fi-section-header-after-ctn { margin-left: auto; flex-shrink: 0; align-self: flex-start; }
                        @media (max-width: 640px) {
                            .fi-wi-chart .fi-section-header-content { flex-wrap: wrap; }
                        }
                        /* Sales chart period dropdown */
                        .tyt-pd { position: relative; }
                        .tyt-pd-btn { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 10px; border: 1px solid rgba(201,168,76,0.55); background: rgba(201,168,76,0.08); color: #111827; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .15s, border-color .15s; }
                        .dark .tyt-pd-btn { color: #fff; }
                        .tyt-pd-btn:hover, .tyt-pd-btn[aria-expanded="true"] { background: rgba(201,168,76,0.16); border-color: #c9a84c; }
                        .tyt-pd-btn:focus-visible { outline: 2px solid #c9a84c; outline-offset: 2px; }
                        .tyt-pd-ico { width: 15px; height: 15px; color: #c9a84c; }
                        .tyt-pd-chev { width: 16px; height: 16px; color: #c9a84c; transition: transform .2s; }
                        .tyt-pd-chev.is-open { transform: rotate(180deg); }
                        .tyt-pd-menu { position: absolute; right: 0; top: calc(100% + 6px); z-index: 30; min-width: 190px; padding: 6px; border-radius: 12px; background: #fff; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 16px 40px rgba(0,0,0,0.18); }
                        .dark .tyt-pd-menu { background: #1f2430; border-color: rgba(201,168,76,0.3); box-shadow: 0 16px 40px rgba(0,0,0,0.55); }
                        .tyt-pd-opt { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 12px; border-radius: 8px; border: none; background: none; text-align: left; font-size: 13px; color: #374151; cursor: pointer; }
                        .dark .tyt-pd-opt { color: #d1d5db; }
                        .tyt-pd-opt:hover, .tyt-pd-opt:focus-visible { background: rgba(201,168,76,0.12); color: #111827; outline: none; }
                        .dark .tyt-pd-opt:hover, .dark .tyt-pd-opt:focus-visible { color: #fff; }
                        .tyt-pd-opt.is-on { background: rgba(201,168,76,0.18); color: #8a6d1f; font-weight: 600; }
                        .dark .tyt-pd-opt.is-on { color: #e8c96b; }
                        .tyt-pd-opt svg { width: 15px; height: 15px; color: #c9a84c; flex-shrink: 0; }
                        [x-cloak] { display: none !important; }
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

                        /* Phones: fit booking no., guest, amount and status on
                           screen — "Open" goes (tapping the row opens it),
                           long status labels wrap, cells sit closer. */
                        @media (max-width: 767px) {
                            .tyt-hide-on-phone { display: none !important; }
                            .fi-ta-cell { padding-left: 4px !important; padding-right: 4px !important; }
                            .fi-ta-cell .fi-badge { white-space: normal !important; text-align: left; }
                            .fi-ta-cell .fi-badge .fi-badge-label-ctn, .fi-ta-cell .fi-badge span { white-space: normal !important; }
                            .fi-ta-cell .fi-ta-text, .fi-ta-cell .fi-ta-text * { white-space: normal !important; }
                            .fi-ta-cell .fi-ta-text { min-width: 0 !important; }
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
