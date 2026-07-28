<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /** @use HasFactory<\Database\Factories\PackageFactory> */
    use HasFactory;

    protected $guarded = [];

    public function destination() { return $this->belongsTo(Destination::class); }
    public function inclusions() { return $this->hasMany(PackageInclusion::class); }
    public function images() { return $this->hasMany(PackageImage::class); }
}
