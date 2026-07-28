<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaycationImage extends Model
{
    /** @use HasFactory<\Database\Factories\StaycationImageFactory> */
    use HasFactory;

    public function staycation() { return $this->belongsTo(Staycation::class); }
}
