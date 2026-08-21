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
        'is_upto'      => 'boolean',
        'min_order_value' => 'decimal:2',
        'upto_options' => 'array',
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
     * Human-readable discount label e.g. "20% OFF" or "₹500 OFF" or "Up to 50% OFF"
     */
    public function getDiscountLabelAttribute(): string
    {
        $prefix = $this->is_upto ? 'Up to ' : '';
        if ($this->discount_type === 'percentage') {
            return $prefix . $this->discount_value . '% OFF';
        }
        return $prefix . '₹' . number_format($this->discount_value, 0) . ' OFF';
    }

    /**
     * Calculates the actual discount value based on order amount and 'up to' logic.
     */
    public function calculateAppliedDiscount($orderValue = 0)
    {
        if ($this->min_order_value && $orderValue < $this->min_order_value) {
            return 0; // Does not meet minimum order value
        }

        if (!$this->is_upto || empty($this->upto_options)) {
            return $this->discount_value;
        }

        // Randomly select one of the valid options
        $options = $this->upto_options;
        return $options[array_rand($options)];
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('valid_to', '>=', now()->toDateString());
    }
}
