<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageImage extends Model
{
    /** @use HasFactory<\Database\Factories\PackageImageFactory> */
    use HasFactory;

    protected $guarded = [];
    protected $touches = ['package'];

    public function package() { return $this->belongsTo(Package::class, 'package_id'); }

    public function getImagePathAttribute()
    {
        if (\Illuminate\Support\Str::startsWith($this->path, 'http')) {
            return $this->path;
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->path);
    }
}
