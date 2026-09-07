<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    /** @use HasFactory<\Database\Factories\HotelFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'house_rules' => 'array',
        'description_sections' => 'array',
        'bottom_sections' => 'array',
    ];

    public function destination() { return $this->belongsTo(Destination::class); }
    public function roomTypes() { return $this->hasMany(RoomType::class); }
    public function images() { return $this->hasMany(HotelImage::class); }
    public function amenities() { return $this->belongsToMany(Amenity::class); }
}
