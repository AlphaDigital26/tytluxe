<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    /** @use HasFactory<\Database\Factories\RoomTypeFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'inclusions' => 'array',
        'images' => 'array',
        'manual_images' => 'array',
        'hidden_images' => 'array',
        'is_active' => 'boolean',
    ];

    public function hotel() { return $this->belongsTo(Hotel::class); }

    /**
     * `images` (TripJack API sync) and `manual_images` (admin uploads via
     * Filament) are stored separately so they never clobber each other —
     * see the manual_images migration for why. Every place on the site
     * that displays a room's gallery should read this, not either column
     * directly, so API and manual photos always coexist — minus whatever
     * the admin has hidden via `hidden_images` (see its migration).
     */
    public function getAllImagesAttribute(): array
    {
        $hidden = collect($this->hidden_images ?? []);

        return collect($this->images ?? [])
            ->reject(fn ($url) => $hidden->contains($url))
            ->merge($this->manual_images ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
