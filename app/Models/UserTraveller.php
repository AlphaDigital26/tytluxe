<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTraveller extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'first_name',
        'last_name',
        'relationship',
        'dob',
        'gender',
        'nationality',
        'meal_preference',
        'train_berth_preference',
        'passport_number',
        'passport_expiry',
        'passport_issuing_country',
        'phone',
        'email',
        'frequent_flyer_airline',
        'frequent_flyer_number',
    ];

    protected $casts = [
        'dob' => 'date',
        'passport_expiry' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
