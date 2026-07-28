<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CruiseImage extends Model
{
    /** @use HasFactory<\Database\Factories\CruiseImageFactory> */
    use HasFactory;

    protected $guarded = [];

    public function cruise() { return $this->belongsTo(Cruise::class); }
}
