<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staycation extends Model
{
    /** @use HasFactory<\Database\Factories\StaycationFactory> */
    use HasFactory;

    public function destination() { return $this->belongsTo(Destination::class); }
    public function images() { return $this->hasMany(StaycationImage::class); }
    public function amenities() { return $this->belongsToMany(Amenity::class); }
}
