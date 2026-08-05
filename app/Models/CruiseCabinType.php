<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CruiseCabinType extends Model
{
    /** @use HasFactory<\Database\Factories\CruiseCabinTypeFactory> */
    use HasFactory;

    protected $guarded = [];

    public function cruise() { return $this->belongsTo(Cruise::class); }

    /**
     * Resolve the best available image URL:
     * - Prefer uploaded file if it exists on disk.
     * - Fall back to external image_url.
     * - Otherwise return null.
     */
    public function getResolvedImageAttribute(): ?string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }
        return $this->image_url ?: null;
    }
}
