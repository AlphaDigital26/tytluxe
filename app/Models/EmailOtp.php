<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * The code as stored: a keyed hash, never the code itself, so a copy of
     * the database doesn't expose codes that still work.
     */
    public static function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /** Whether $code is this record's code. */
    public function matches(string $code): bool
    {
        return hash_equals($this->otp, self::hashCode($code));
    }

    /**
     * Check if this OTP has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if maximum attempts have been exceeded.
     */
    public function isMaxAttemptsExceeded(): bool
    {
        return $this->attempts >= 5;
    }
}
