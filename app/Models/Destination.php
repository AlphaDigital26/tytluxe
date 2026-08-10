<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    /** @use HasFactory<\Database\Factories\DestinationFactory> */
    use HasFactory;

    protected $guarded = [];

    public function hotels() { return $this->hasMany(Hotel::class); }
    public function cruises() { return $this->hasMany(Cruise::class); }
    public function packages() { return $this->hasMany(Package::class); }
}
