<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    /** @use HasFactory<\Database\Factories\EnquiryFactory> */
    use HasFactory, \App\Traits\ResolvesVerticalReference;

    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
    public function assignedAgent() { return $this->belongsTo(Admin::class, 'assigned_agent_id'); }
    public function offers() { return $this->hasMany(Offer::class); }
}
