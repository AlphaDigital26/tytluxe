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
        // Typed-in places get a capital letter: "udaipur" → "Udaipur".
        return $this->hotel ? $this->hotel->destination?->name : (filled($this->location) ? Str::ucfirst(trim($this->location)) : null);
    }

    /**
     * Which trip days each stay covers, in trip order: a stay starts on its
     * own "check-in day" if one is set, else on the day the stay before it
     * ends (the first on Day 1); it ends that many nights later.
     *
     * @param  iterable<int|string, array{nights?: mixed, check_in_day?: mixed}|self>  $stays  form rows or models, in order
     * @return array<int|string, array{in: int, out: ?int}>  keyed like $stays
     */
    public static function dayPlan(iterable $stays): array
    {
        $plan = [];
        $next = 1;
        foreach ($stays as $key => $stay) {
            $row = $stay instanceof self ? $stay->only(['nights', 'check_in_day']) : (array) $stay;
            $in = filled($row['check_in_day'] ?? null) ? max(0, (int) $row['check_in_day']) : $next;
            $nights = (int) ($row['nights'] ?? 0);
            $out = $nights > 0 ? $in + $nights : null;
            $plan[$key] = ['in' => $in, 'out' => $out];
            $next = $out ?? $in;
        }

        return $plan;
    }

    /** "2:00 PM" — the linked hotel's own time, else the one typed in here. */
    public function checkInTime(): ?string
    {
        return self::niceTime($this->hotel ? $this->hotel->check_in_time : $this->check_in_time);
    }

    public function checkOutTime(): ?string
    {
        return self::niceTime($this->hotel ? $this->hotel->check_out_time : $this->check_out_time);
    }

    /** "14:00" / "14:00:00" / "2:00 PM" → "2:00 PM"; anything unreadable is shown as typed. */
    protected static function niceTime(?string $time): ?string
    {
        $time = trim((string) $time);
        if ($time === '') {
            return null;
        }
        try {
            return \Illuminate\Support\Carbon::parse($time)->format('g:i A');
        } catch (\Throwable) {
            return $time;
        }
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
