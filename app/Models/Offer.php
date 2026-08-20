<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Offer extends Model
{
    /** @use HasFactory<\Database\Factories\OfferFactory> */
    use HasFactory, \App\Traits\ResolvesVerticalReference;

    protected $guarded = [];

    protected $casts = [
        'valid_from'   => 'date',
        'valid_to'     => 'date',
        'is_active'    => 'boolean',
        'coming_soon'  => 'boolean',
        'discount_value' => 'decimal:2',
        'sort_order'   => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function enquiry() { return $this->belongsTo(Enquiry::class); }
    public function agent()   { return $this->belongsTo(User::class, 'agent_id'); }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Returns the resolved public image URL:
     * uploaded file takes priority over external URL.
     */
    public function getResolvedImageAttribute(): ?string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }
        return $this->image_url ?: null;
    }

    /**
     * Human-readable discount label e.g. "20% OFF" or "₹500 OFF"
     */
    public function getDiscountLabelAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value . '% OFF';
        }
        return '₹' . number_format($this->discount_value, 0) . ' OFF';
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('valid_from', '<=', now())
                     ->where('valid_to',   '>=', now());
    }
}
