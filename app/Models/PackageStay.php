<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One stop where a package's guests sleep. Either linked to a catalogue
 * hotel — its name, stars and city are used as they are, and its photo
 * unless one is uploaded here — or typed in by hand (camps, homestays…).
 */
class PackageStay extends Model
{
    protected $guarded = [];

    protected $casts = [
        'or_similar' => 'boolean',
        'nights' => 'integer',
        'star_rating' => 'integer',
    ];

    public const TYPES = [
        'hotel' => 'Hotel',
        'resort' => 'Resort',
        'camp' => 'Camp',
        'homestay' => 'Homestay',
        'cottage' => 'Cottage',
        'houseboat' => 'Houseboat',
        'other' => 'Other',
    ];

    public function package() { return $this->belongsTo(Package::class); }
    public function hotel() { return $this->belongsTo(Hotel::class); }

    // A stay linked to a hotel always shows that hotel's own name, stars and
    // city (the admin form locks those boxes); only a typed-in stay uses its own.

    public function displayName(): string
    {
        return $this->hotel?->title ?: ($this->name ?: 'Stay');
    }

    public function displayStars(): ?int
    {
        return $this->hotel ? ($this->hotel->star_rating ?: null) : ($this->star_rating ?: null);
    }

    public function displayLocation(): ?string
    {
        return $this->hotel ? $this->hotel->destination?->name : $this->location;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->stay_type] ?? 'Stay';
    }

    /** Its own photo, else the linked hotel's first visible photo. */
    public function imageUrl(): ?string
    {
        $path = $this->image ?: $this->hotel?->images?->first()?->path;
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : Storage::disk('public')->url($path);
    }

    /** The linked hotel's page, only when guests can actually see it. */
    public function hotelUrl(): ?string
    {
        $hotel = $this->hotel;
        if (! $hotel || ! $hotel->slug || ! $hotel->is_active) {
            return null;
        }
        if ($hotel->star_rating && ! in_array((int) $hotel->star_rating, array_map('intval', Hotel::allowedStarRatings()), true)) {
            return null;
        }

        return route('hotel.details', $hotel->slug);
    }
}
