<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    /** @use HasFactory<\Database\Factories\AmenityFactory> */
    use HasFactory;

    public function hotels() { return $this->belongsToMany(Hotel::class, 'hotel_amenity'); }
    public function staycations() { return $this->belongsToMany(Staycation::class, 'staycation_amenity'); }
}
