<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageImage extends Model
{
    /** @use HasFactory<\Database\Factories\PackageImageFactory> */
    use HasFactory;

    public function packageModel() { return $this->belongsTo(Package::class, 'package_id'); }
}
