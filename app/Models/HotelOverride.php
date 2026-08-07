<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelOverride extends Model
{
    protected $fillable = [
        'hotel_id',
        'override_name',
        'override_description',
        'override_image',
        'override_amenities',
    ];

    protected $casts = [
        'override_amenities' => 'array',
    ];
}
