<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Destinations\DestinationResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Hotels\HotelResource;
use App\Filament\Resources\Packages\PackageResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Destination;
use App\Models\Enquiry;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\Review;

/** What the website currently offers — shown next to the enquiry mix chart. */
class DashboardStatsOverview extends MetricCardsWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected function heading(): array
    {
        return ['Your website at a glance', '🌐'];
    }

    protected function minCardWidth(): int
    {
        return 150;
    }

    protected function cards(): array
    {
        $url = fn (string $resource) => $resource::canViewAny() ? $resource::getUrl('index') : null;

        $hotels = Hotel::visibleOnWebsite()->count();
        $hidden = Hotel::count() - $hotels;
        $pendingReviews = Review::where('is_published', false)->count();

        return [
            ['icon' => '🏨', 'label' => 'Hotels on website', 'value' => number_format($hotels),
                'hint' => $hidden ? number_format($hidden).' hidden' : 'All visible', 'tone' => 'green', 'url' => $url(HotelResource::class)],
            ['icon' => '🎒', 'label' => 'Holiday packages', 'value' => number_format(Package::where('is_active', true)->count()),
                'hint' => 'Live on website', 'tone' => 'blue', 'url' => $url(PackageResource::class)],
            ['icon' => '📍', 'label' => 'Destinations', 'value' => number_format(Destination::where('is_active', true)->count()),
                'hint' => 'Visible to guests', 'tone' => 'amber', 'url' => $url(DestinationResource::class)],
            ['icon' => '📬', 'label' => 'Total enquiries', 'value' => number_format(Enquiry::count()),
                'hint' => 'Since the start', 'tone' => 'gold', 'url' => $url(EnquiryResource::class)],
            ['icon' => '⭐', 'label' => 'Guest reviews', 'value' => number_format(Review::where('is_published', true)->count()),
                'hint' => $pendingReviews ? $pendingReviews.' waiting for approval' : 'Shown on website', 'tone' => $pendingReviews ? 'amber' : 'violet',
                'url' => $url(ReviewResource::class)],
            ['icon' => '📞', 'label' => 'Not contacted yet', 'value' => number_format(Enquiry::where('status', 'new')->count()),
                'hint' => 'New enquiries', 'tone' => 'red', 'url' => EnquiryResource::canViewAny() ? EnquiryResource::getUrl('index').'?activeTab=new' : null],
        ];
    }
}
