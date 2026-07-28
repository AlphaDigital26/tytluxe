<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CruiseItineraryDay extends Model
{
    /** @use HasFactory<\Database\Factories\CruiseItineraryDayFactory> */
    use HasFactory;

    public function cruise() { return $this->belongsTo(Cruise::class); }
}
