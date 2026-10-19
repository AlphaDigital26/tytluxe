<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    /** @use HasFactory<\Database\Factories\AmenityFactory> */
    use HasFactory;

    protected $guarded = [];

    // ── Type labels & colours (used in Filament) ──────────────────────────
    public const TYPES = [
        'hotel'   => 'Hotel',
        'cruise'  => 'Cruise',
        'package' => 'Package',
    ];

    public const TYPE_COLORS = [
        'hotel'   => 'info',
        'cruise'  => 'success',
        'package' => 'warning',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function hotels()    { return $this->belongsToMany(Hotel::class,   'amenity_hotel'); }
    public function cruises()   { return $this->belongsToMany(Cruise::class,  'amenity_cruise'); }
    public function packages()  { return $this->belongsToMany(Package::class, 'amenity_package'); }

    /** @var array<string, true>|null lower-cased hotel titles, loaded once per request/command */
    protected static ?array $hotelTitles = null;

    /**
     * True when an "amenity" from TripJack is really a hotel or brand name
     * rather than a facility: this hotel's own title, any hotel in the
     * catalogue, or a name ending in Resort / Hotel(s) / Collection
     * ("Larisa Collection", "Mountain Resort", "Official Hotel" — checked
     * against the live list: no real facility ends that way).
     */
    public static function looksLikeHotelName(string $name, ?string $ownHotelTitle = null): bool
    {
        $name = mb_strtolower(trim($name));
        if ($name === '') {
            return false;
        }
        if ($ownHotelTitle !== null && $name === mb_strtolower(trim($ownHotelTitle))) {
            return true;
        }
        if (preg_match('/\b(resorts?|hotels?|[ck]ollection)$/u', $name)) {
            return true;
        }

        static::$hotelTitles ??= Hotel::query()->pluck('title')
            ->mapWithKeys(fn ($t) => [mb_strtolower(trim((string) $t)) => true])->all();

        return isset(static::$hotelTitles[$name]);
    }

    public static function forgetHotelTitles(): void
    {
        static::$hotelTitles = null;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeForHotels($q)   { return $q->where('type', 'hotel'); }
    public function scopeForCruises($q)  { return $q->where('type', 'cruise'); }
    public function scopeForPackages($q) { return $q->where('type', 'package'); }
}
