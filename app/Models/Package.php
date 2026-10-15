<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /** @use HasFactory<\Database\Factories\PackageFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'departure_from' => 'array',
        'arrival_cities' => 'array',
        'notes' => 'array',
        'sharing_prices' => 'array',
        'online_booking' => 'boolean',
    ];

    /**
     * What a guest can pick, per person: the sharing prices (Quad/Triple/
     * Double…) when the package has them, else the one "Starting From" price.
     *
     * @return array<int, array{label: string, price: float}>
     */
    public function priceOptions(): array
    {
        $options = collect($this->sharing_prices ?? [])
            ->filter(fn ($o) => filled($o['label'] ?? null) && (float) ($o['price'] ?? 0) > 0)
            ->map(fn ($o) => ['label' => trim((string) $o['label']), 'price' => round((float) $o['price'], 2)])
            ->values()
            ->all();

        if ($options === [] && (float) $this->price_from > 0) {
            $options = [['label' => 'Per person', 'price' => round((float) $this->price_from, 2)]];
        }

        return $options;
    }

    /** Guests can book and pay on the website (group tours the client switched on). */
    /**
     * Only a guest with a fully paid booking of this package may review it
     * (not one who has paid only the booking amount, nor a cancelled one).
     */
    public function canBeReviewedBy(?int $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        // Fully paid trips that have ended (return date, or departure when no return date).
        $tripsTaken = Booking::where('user_id', $userId)
            ->where('vertical', 'package')
            ->where('package_id', $this->id)
            ->where('status', 'confirmed')
            ->whereNull('cancellation_requested_at')
            ->where(fn ($q) => $q->whereNull('package_balance_amount')->orWhere('package_balance_amount', '<=', 0))
            ->where(fn ($q) => $q->whereDate('check_out', '<', today())
                ->orWhere(fn ($q) => $q->whereNull('check_out')->whereDate('check_in', '<', today())))
            ->count();

        // One review per trip taken.
        $reviewsWritten = $this->reviews()->where('user_id', $userId)->count();

        return $tripsTaken > $reviewsWritten;
    }

    public function acceptsOnlineBooking(): bool
    {
        return $this->online_booking && $this->is_active && $this->tour_type !== 'custom' && $this->priceOptions() !== [];
    }

    /** Departures still open for online booking, soonest first. */
    public function bookableDepartures()
    {
        return $this->departures->filter(fn (PackageDeparture $d) => $d->isOpenForBooking())->values();
    }

    public function destination()   { return $this->belongsTo(Destination::class); }
    public function inclusions()    { return $this->hasMany(PackageInclusion::class); }
    public function images()        { return $this->hasMany(PackageImage::class); }
    public function reviews()       { return $this->hasMany(Review::class, 'reference_id')->where('vertical', 'package'); }
    public function itineraryDays() { return $this->hasMany(PackageItineraryDay::class)->orderBy('sort_order')->orderBy('day_number'); }
    public function highlights()    { return $this->hasMany(PackageHighlight::class)->orderBy('sort_order'); }
    public function exclusions()    { return $this->hasMany(PackageExclusion::class)->orderBy('sort_order'); }
    public function departures()    { return $this->hasMany(PackageDeparture::class)->orderBy('start_date'); }
    public function amenities()     { return $this->belongsToMany(Amenity::class, 'amenity_package'); }

    public function getHeroImageUrlAttribute()
    {
        if ($this->hero_bg_image) {
            if (\Illuminate\Support\Str::startsWith($this->hero_bg_image, 'http')) {
                return $this->hero_bg_image;
            }
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->hero_bg_image);
        }

        if ($this->images && $this->images->count() > 0) {
            return $this->images->first()->image_path;
        }

        return 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=1800&q=85';
    }
}
