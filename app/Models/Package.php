<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /** @use HasFactory<\Database\Factories\PackageFactory> */
    use HasFactory;

    protected $guarded = [];

    public function destination() { return $this->belongsTo(Destination::class); }
    public function inclusions() { return $this->hasMany(PackageInclusion::class); }
    public function images() { return $this->hasMany(PackageImage::class); }
    public function reviews() { return $this->hasMany(Review::class, 'reference_id')->where('vertical', 'package'); }
    public function itineraryDays() { return $this->hasMany(PackageItineraryDay::class)->orderBy('sort_order')->orderBy('day_number'); }
    public function highlights() { return $this->hasMany(PackageHighlight::class)->orderBy('sort_order'); }
    public function exclusions() { return $this->hasMany(PackageExclusion::class)->orderBy('sort_order'); }
    public function departures() { return $this->hasMany(PackageDeparture::class)->orderBy('start_date'); }
}
