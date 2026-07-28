<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    /** @use HasFactory<\Database\Factories\OfferFactory> */
    use HasFactory, \App\Traits\ResolvesVerticalReference;

    protected $guarded = [];

    public function enquiry() { return $this->belongsTo(Enquiry::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
}
