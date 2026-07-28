<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CruiseCabinType extends Model
{
    /** @use HasFactory<\Database\Factories\CruiseCabinTypeFactory> */
    use HasFactory;

    public function cruise() { return $this->belongsTo(Cruise::class); }
}
