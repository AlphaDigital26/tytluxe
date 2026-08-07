<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \Illuminate\Database\Eloquent\SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'phone',
        'password',
        'status',
        'last_login_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'dob',
        'gender',
        'address',
        'preferences',
        'notifications',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'address' => 'array',
            'preferences' => 'array',
            'notifications' => 'array',
            'dob' => 'date',
        ];
    }

    public function savedTravellers() { return $this->hasMany(UserTraveller::class); }
    public function enquiries() { return $this->hasMany(Enquiry::class); }
    public function assignedEnquiries() { return $this->hasMany(Enquiry::class, 'agent_id'); }
    public function offers() { return $this->hasMany(Offer::class, 'agent_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function assignedBookings() { return $this->hasMany(Booking::class, 'agent_id'); }
    public function reviews() { return $this->hasMany(Review::class); }

    public function getUserIdAttribute(): string
    {
        return 'TYT' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return false;
    }
}
