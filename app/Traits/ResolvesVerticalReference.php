<?php

namespace App\Traits;

use App\Models\Hotel;
use App\Models\Flight;
use App\Models\Cruise;
use App\Models\Staycation;
use App\Models\Package;

trait ResolvesVerticalReference
{
    /**
     * Get the owning vertical model (e.g., Hotel, Cruise, Staycation, Package).
     */
    public function verticalModel()
    {
        $vertical = strtolower($this->vertical);

        return match ($vertical) {
            'hotel' => $this->belongsTo(Hotel::class, 'reference_id'),
            // 'flight' => $this->belongsTo(Flight::class, 'reference_id'), // Flight model will come later
            'cruise' => $this->belongsTo(Cruise::class, 'reference_id'),
            'staycation' => $this->belongsTo(Staycation::class, 'reference_id'),
            'package' => $this->belongsTo(Package::class, 'reference_id'),
            default => null,
        };
    }
}
