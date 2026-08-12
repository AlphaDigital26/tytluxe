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

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeForHotels($q)   { return $q->where('type', 'hotel'); }
    public function scopeForCruises($q)  { return $q->where('type', 'cruise'); }
    public function scopeForPackages($q) { return $q->where('type', 'package'); }
}
