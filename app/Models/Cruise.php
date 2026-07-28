<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cruise extends Model
{
    /** @use HasFactory<\Database\Factories\CruiseFactory> */
    use HasFactory;

    protected $guarded = [];

    public function destination() { return $this->belongsTo(Destination::class); }
    public function itineraryDays() { return $this->hasMany(CruiseItineraryDay::class); }
    public function cabinTypes() { return $this->hasMany(CruiseCabinType::class); }
    public function images() { return $this->hasMany(CruiseImage::class); }
}
