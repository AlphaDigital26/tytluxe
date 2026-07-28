<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageInclusion extends Model
{
    /** @use HasFactory<\Database\Factories\PackageInclusionFactory> */
    use HasFactory;

    protected $guarded = [];

    public function packageModel() { return $this->belongsTo(Package::class, 'package_id'); }
}
