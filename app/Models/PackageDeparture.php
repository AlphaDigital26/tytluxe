<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageDeparture extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $touches = ['package'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
