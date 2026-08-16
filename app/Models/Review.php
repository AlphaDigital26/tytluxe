<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory, \App\Traits\ResolvesVerticalReference;

    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
