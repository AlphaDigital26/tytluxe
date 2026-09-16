<?php

namespace App\Filament\Pages;

use App\Models\Hotel;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HotelListingSettings extends Page
{
    protected string $view = 'filament.pages.hotel-listing-settings';

    protected static ?int $navigationSort = 41;

    protected static ?string $navigationLabel = 'Hotel Listing Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $title = 'Hotel Listing Settings';

    // -----------------------------------------------------------------
    // Form State
    // -----------------------------------------------------------------
    public array $data = [];

    public function mount(): void
    {
        $this->data = [
            'allowed_star_ratings' => Hotel::allowedStarRatings(),
        ];
    }

    // -----------------------------------------------------------------
    // Form
    // -----------------------------------------------------------------
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('⭐ Star Rating Filter')
                    ->description('Control which hotel star ratings are allowed to appear anywhere on the public website — search results, hotel detail pages, wishlist, and booking. Hotels with an unchecked star rating stay in the CMS untouched and remain fully manageable here, they just will not show to visitors.')
                    ->schema([
                        CheckboxList::make('allowed_star_ratings')
                            ->label('Star Ratings Visible on the Website')
                            ->options([
                                1 => '⭐ 1 Star',
                                2 => '⭐⭐ 2 Stars',
                                3 => '⭐⭐⭐ 3 Stars',
                                4 => '⭐⭐⭐⭐ 4 Stars',
                                5 => '⭐⭐⭐⭐⭐ 5 Stars',
                            ])
                            ->required()
                            ->columns(5)
                            ->helperText('e.g. tick only 4 and 5 Stars to show visitors just your 4-star and 5-star hotels. Untick a rating to instantly hide every hotel at that rating from the site.'),
                    ]),
            ]);
    }

    // -----------------------------------------------------------------
    // Save Action
    // -----------------------------------------------------------------
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Hotel Listing Settings')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $ratings = array_values(array_map('intval', $this->data['allowed_star_ratings'] ?? []));

        if (empty($ratings)) {
            Notification::make()
                ->title('At least one star rating must stay checked')
                ->body('Otherwise every hotel on the site would be hidden from visitors.')
                ->danger()
                ->send();

            return;
        }

        Setting::setJson('hotel_listing.allowed_star_ratings', $ratings);

        Notification::make()
            ->title('Hotel listing settings saved!')
            ->body('The website now shows only hotels rated: ' . implode(', ', array_map(fn ($r) => $r . '★', $ratings)) . '.')
            ->success()
            ->send();
    }
}
