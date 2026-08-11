<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageItineraryDay extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'chips' => 'array',
    ];

    public function package() { return $this->belongsTo(Package::class); }
}
