<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TripJackNotifications extends Page implements HasTable
{

    protected static string|\UnitEnum|null $navigationGroup = 'Customers & Team';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Alerts';
    use InteractsWithTable;

    protected string $view = 'filament.pages.tripjack-notifications';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $title = 'Alerts';

    public function getSubheading(): ?string
    {
        return 'Automatic warnings from the website — for example when the TripJack wallet is running low or TripJack is limiting requests. Mark an alert as read once you have dealt with it.';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Auth::guard('admin')->user()?->unreadNotifications()->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Auth::guard('admin')->user()
                    ->notifications()
                    ->getQuery()
            )
            ->columns([
                TextColumn::make('data.title')
                    ->label('Alert')
                    ->weight(fn ($record) => is_null($record->read_at) ? 'bold' : 'normal'),
                TextColumn::make('data.body')
                    ->label('Details')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('M j, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('markAsRead')
                    ->label('Mark read')
                    ->icon(Heroicon::Check)
                    ->visible(fn ($record) => is_null($record->read_at))
                    ->action(fn ($record) => $record->markAsRead()),
            ])
            ->headerActions([
                Action::make('markAllAsRead')
                    ->label('Mark all as read')
                    ->icon(Heroicon::CheckCircle)
                    ->action(fn () => Auth::guard('admin')->user()->unreadNotifications()->update(['read_at' => now()])),
            ])
            ->emptyStateHeading('No alerts yet')
            ->emptyStateDescription('Warnings such as a low TripJack wallet balance will show up here.')
            ->emptyStateIcon('heroicon-o-bell-slash');
    }
}
