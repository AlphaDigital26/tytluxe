<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelImage extends Model
{
    /** @use HasFactory<\Database\Factories\HotelImageFactory> */
    use HasFactory;

    public function hotel() { return $this->belongsTo(Hotel::class); }
}
