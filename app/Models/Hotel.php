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
        'rating_score' => 'float',
        'review_count' => 'integer',
    ];

    public function destination() { return $this->belongsTo(Destination::class); }
    public function roomTypes() { return $this->hasMany(RoomType::class); }
    public function images() { return $this->hasMany(HotelImage::class); }
    public function amenities() { return $this->belongsToMany(Amenity::class); }
    public function reviews() { return $this->hasMany(Review::class, 'reference_id')->where('vertical', 'hotel')->where('is_published', true); }

    /**
     * The star ratings currently allowed to show on the public site, set by
     * the admin in Hotel Listing Settings. Admin/Filament queries never use
     * this — only the public-facing scope below does — so admins can still
     * see and manage every hotel regardless of this filter.
     */
    public static function allowedStarRatings(): array
    {
        return Setting::getJson('hotel_listing.allowed_star_ratings', [1, 2, 3, 4, 5]);
    }

    /**
     * Active hotels whose star rating is currently allowed on the website.
     * Use this (instead of a bare `where('is_active', true)`) everywhere a
     * hotel is queried for public display.
     */
    public function scopeVisibleOnWebsite($query)
    {
        return $query->where('is_active', true)->whereIn('star_rating', static::allowedStarRatings());
    }
}
