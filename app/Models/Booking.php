<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
    public function travelers() { return $this->hasMany(BookingTraveler::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function package() { return $this->belongsTo(Package::class); }
}
