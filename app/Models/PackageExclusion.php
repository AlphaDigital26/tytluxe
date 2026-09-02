<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageExclusion extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $touches = ['package'];

    public function package() { return $this->belongsTo(Package::class); }
}
