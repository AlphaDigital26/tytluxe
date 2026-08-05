<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CruiseImage extends Model
{
    /** @use HasFactory<\Database\Factories\CruiseImageFactory> */
    use HasFactory;

    protected $guarded = [];

    public function cruise() { return $this->belongsTo(Cruise::class); }

    /**
     * Resolve the best available image URL:
     * - Prefer uploaded file (path) if it exists on the public disk.
     * - Fall back to external image_url.
     */
    public function getResolvedImageAttribute(): ?string
    {
        if ($this->path && Storage::disk('public')->exists($this->path)) {
            return Storage::disk('public')->url($this->path);
        }
        return $this->image_url ?: null;
    }
}
