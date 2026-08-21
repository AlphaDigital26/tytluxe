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
    ];

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
